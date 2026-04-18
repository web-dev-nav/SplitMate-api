<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
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
            'category' => 'required|in:food,transport,entertainment,utilities,accommodation,shopping,healthcare,other',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'string|exists:users,uuid',
        ]);

        // Convert user UUIDs to user IDs
        $paidByUser = \App\Models\User::where('uuid', $validated['paid_by_user_id'])->firstOrFail();

        // Get active group members if participants not specified
        if (empty($validated['participant_ids'])) {
            $participantIds = $group->members()
                ->wherePivot('is_active', true)
                ->pluck('uuid')
                ->toArray();
        } else {
            $participantIds = $validated['participant_ids'];
        }

        // Create expense
        $expense = Expense::create([
            'uuid' => Str::uuid(),
            'group_id' => $group->id,
            'title' => $validated['title'],
            'amount_cents' => $validated['amount_cents'],
            'paid_by_user_id' => $paidByUser->id,
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'participant_ids' => $participantIds,
            'user_count_at_time' => $group->members()->wherePivot('is_active', true)->count(),
        ]);
        $expense->load('paidByUser');

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

        if ($expense->receipt_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->receipt_photo);
        }

        $expense->update(['receipt_photo' => null]);

        return response()->json([
            'expense' => ApiPayload::expense($expense->load('paidByUser')),
            'message' => 'Receipt deleted successfully',
        ]);
    }
}
