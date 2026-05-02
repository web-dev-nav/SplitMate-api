<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ExpenseCreatedMail;
use App\Models\Expense;
use App\Models\Group;
use App\Models\StatementRecord;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * List all expenses in a group.
     */
    public function index(Request $request, Group $group)
    {
        $expenses = $group->expenses()
            ->with('paidByUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'expenses' => collect($expenses->items())->map(fn (Expense $expense) => ApiPayload::expense($expense))->values(),
            'pagination' => [
                'total' => $expenses->total(),
                'per_page' => $expenses->perPage(),
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new expense.
     */
    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount_cents' => 'required|integer|min:1',
            'paid_by_user_id' => 'required|string|exists:users,uuid',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:50',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'string|exists:users,uuid',
        ]);

        $activeMemberUuids = $group->members()
            ->wherePivot('is_active', true)
            ->pluck('uuid')
            ->toArray();

        $category = strtolower(trim($validated['category']));
        $allowedCategories = $group->expense_categories ?? Group::defaultExpenseCategories();
        if (!in_array($category, $allowedCategories, true)) {
            return response()->json([
                'message' => 'The selected category is invalid for this group.',
                'errors' => [
                    'category' => ['The selected category is invalid for this group.'],
                ],
            ], 422);
        }

        // Convert user UUIDs to user IDs
        $paidByUser = \App\Models\User::where('uuid', $validated['paid_by_user_id'])->firstOrFail();
        if (!in_array($paidByUser->uuid, $activeMemberUuids, true)) {
            return response()->json([
                'message' => 'Payer must be an active group member.',
                'errors' => [
                    'paid_by_user_id' => ['Payer must be an active group member.'],
                ],
            ], 422);
        }

        // Get active group members if participants not specified
        if (empty($validated['participant_ids'])) {
            $participantIds = $activeMemberUuids;
        } else {
            $participantIds = array_values(array_unique($validated['participant_ids']));
            $invalidParticipants = array_values(array_diff($participantIds, $activeMemberUuids));
            if (!empty($invalidParticipants)) {
                return response()->json([
                    'message' => 'All participants must be active members of this group.',
                    'errors' => [
                        'participant_ids' => ['All participants must be active members of this group.'],
                    ],
                ], 422);
            }
        }

        // Create expense + statement records atomically.
        $expense = DB::transaction(function () use ($group, $validated, $paidByUser, $category, $participantIds, $activeMemberUuids) {
            $expense = Expense::create([
                'uuid' => Str::uuid(),
                'group_id' => $group->id,
                'title' => $validated['title'],
                // Backward compatibility for legacy schema where description/amount are required.
                'description' => $validated['title'],
                'amount_cents' => $validated['amount_cents'],
                'amount' => round($validated['amount_cents'] / 100, 2),
                'paid_by_user_id' => $paidByUser->id,
                'expense_date' => $validated['expense_date'],
                'category' => $category,
                'participant_ids' => $participantIds,
                'user_count_at_time' => count($activeMemberUuids),
            ]);

            $this->balanceService->createStatementRecords($group, expense: $expense);

            return $expense;
        });
        $expense->load('paidByUser');

        // Send email notifications to active group members (excluding the payer)
        if ($group->email_notifications) {
            $paidByName = optional($expense->paidByUser)->name ?? 'Someone';
            $activeMembers = $group->members()
                ->wherePivot('is_active', true)
                ->where('users.id', '!=', $paidByUser->id)
                ->whereNotNull('users.email')
                ->get();

            foreach ($activeMembers as $member) {
                try {
                    Mail::to($member->email)->send(
                        new ExpenseCreatedMail($expense, $group, $member, $paidByName)
                    );
                } catch (\Throwable) {
                    // Never fail the request due to email errors
                }
            }
        }

        return response()->json([
            'expense' => ApiPayload::expense($expense),
        ], 201);
    }

    /**
     * Get expense details.
     */
    public function show(Request $request, Group $group, Expense $expense)
    {
        if ($expense->group_id !== $group->id) {
            return response()->json(['message' => 'Expense not found in this group'], 404);
        }

        return response()->json([
            'expense' => ApiPayload::expense($expense->load('paidByUser')),
        ]);
    }

    /**
     * Update participants for an expense.
     */
    public function updateParticipants(Request $request, Group $group, Expense $expense)
    {
        if ($expense->group_id !== $group->id) {
            return response()->json(['message' => 'Expense not found in this group'], 404);
        }

        $validated = $request->validate([
            'participant_ids' => 'required|array|min:2',
            'participant_ids.*' => 'string|exists:users,uuid',
        ]);

        $expense->update([
            'participant_ids' => $validated['participant_ids'],
        ]);

        return response()->json([
            'expense' => ApiPayload::expense($expense->load('paidByUser')),
        ]);
    }

    /**
     * Upload receipt image.
     */
    public function uploadReceipt(Request $request, Group $group, Expense $expense)
    {
        if ($expense->group_id !== $group->id) {
            return response()->json(['message' => 'Expense not found in this group'], 404);
        }

        $validated = $request->validate([
            'receipt_photo' => 'required|image|max:15360', // 15MB
        ]);

        try {
            $path = $request->file('receipt_photo')->store('receipts', 'public');
            $expense->update(['receipt_photo' => $path]);

            return response()->json([
                'expense' => ApiPayload::expense($expense->load('paidByUser')),
                'message' => 'Receipt uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete receipt image.
     */
    public function deleteReceipt(Request $request, Group $group, Expense $expense)
    {
        if ($expense->group_id !== $group->id) {
            return response()->json(['message' => 'Expense not found in this group'], 404);
        }

        if ((int) $expense->paid_by_user_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Only the member who added this expense can modify or delete it.',
            ], 403);
        }

        if ($expense->receipt_photo) {
            Storage::disk('public')->delete($expense->receipt_photo);
        }

        $expense->update(['receipt_photo' => null]);

        return response()->json([
            'expense' => ApiPayload::expense($expense->load('paidByUser')),
            'message' => 'Receipt deleted successfully',
        ]);
    }

    /**
     * Delete an expense record (owner only).
     */
    public function destroy(Request $request, Group $group, Expense $expense)
    {
        if ($expense->group_id !== $group->id) {
            return response()->json(['message' => 'Expense not found in this group'], 404);
        }

        if ((int) $expense->paid_by_user_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Only the member who added this expense can delete it.',
            ], 403);
        }

        DB::transaction(function () use ($expense) {
            StatementRecord::where('expense_id', $expense->id)->delete();

            if ($expense->receipt_photo) {
                Storage::disk('public')->delete($expense->receipt_photo);
            }

            $expense->delete();
        });

        return response()->json([
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
