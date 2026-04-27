<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\Settlement;
use App\Models\StatementRecord;
use App\Models\User;
use App\Models\EmailVerificationCode;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'groups' => Group::count(),
            'expenses' => Expense::count(),
            'settlements' => Settlement::count(),
            'statements' => StatementRecord::count(),
        ];

        $groups = Group::query()
            ->withCount('members', 'expenses', 'settlements')
            ->latest()
            ->get();

        $recentExpenses = Expense::query()
            ->with(['group', 'paidByUser'])
            ->latest()
            ->limit(8)
            ->get();

        $recentSettlements = Settlement::query()
            ->with(['group', 'fromUser', 'toUser'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'groups', 'recentExpenses', 'recentSettlements'));
    }

    public function users(): View
    {
        $users = User::query()->latest()->get();

        return view('admin.users', compact('users'));
    }

    public function editUser(User $user): View
    {
        return view('admin.user-edit', compact('user'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $updates = [
            'name' => trim((string) $validated['name']),
            'email' => $email,
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($email !== strtolower((string) $user->email)) {
            $updates['email_verified_at'] = null;
        }

        if (!empty($validated['password'])) {
            $updates['password'] = Hash::make((string) $validated['password']);
            // Force re-login on password reset done from admin panel.
            $user->tokens()->delete();
        }

        $user->update($updates);

        return redirect()->route('admin.users')->with('status', 'User updated successfully.');
    }

    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with('status', 'User status updated.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        if (Group::where('created_by_user_id', $user->id)->exists()) {
            return back()->with('status', 'Cannot delete user: they are creator of one or more groups.');
        }

        $hasTransactions = Expense::where('paid_by_user_id', $user->id)->exists()
            || Settlement::where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)->exists()
            || StatementRecord::where('user_id', $user->id)->exists();

        if ($hasTransactions) {
            return back()->with('status', 'Cannot delete user: user has transaction history.');
        }

        DB::transaction(function () use ($user) {
            $user->groups()->detach();
            $user->tokens()->delete();
            EmailVerificationCode::where('user_id', $user->id)->delete();
            GroupInvitation::whereRaw('LOWER(email) = ?', [strtolower((string) $user->email)])->delete();
            GroupInvitation::where('invited_by_user_id', $user->id)->delete();
            $user->delete();
        });

        return back()->with('status', 'User deleted successfully.');
    }

    public function groups(): View
    {
        $groups = Group::query()
            ->with(['creator'])
            ->withCount('members', 'expenses', 'settlements')
            ->latest()
            ->get();

        return view('admin.groups', compact('groups'));
    }

    public function editGroup(Group $group): View
    {
        $group->load([
            'creator',
            'members' => fn ($query) => $query->wherePivot('is_active', true),
        ]);

        return view('admin.group-edit', compact('group'));
    }

    public function updateGroup(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'currency_code' => ['required', 'string', 'size:3'],
            'owner_user_id' => ['required', 'string', 'exists:users,uuid'],
        ]);

        $newOwner = User::where('uuid', $validated['owner_user_id'])->first();
        if (!$newOwner) {
            return back()->with('status', 'Owner user not found.');
        }

        $isActiveMember = $group->members()
            ->where('users.id', $newOwner->id)
            ->wherePivot('is_active', true)
            ->exists();

        if (!$isActiveMember) {
            return back()->with('status', 'Selected owner must be an active member of this group.');
        }

        DB::transaction(function () use ($group, $validated, $newOwner) {
            $group->update([
                'name' => trim((string) $validated['name']),
                'currency_code' => strtoupper((string) $validated['currency_code']),
                'created_by_user_id' => $newOwner->id,
            ]);

            $group->members()->updateExistingPivot($newOwner->id, [
                'role' => 'admin',
                'is_active' => true,
            ]);
        });

        return redirect()->route('admin.groups')->with('status', 'Group updated successfully.');
    }

    public function deleteGroup(Request $request, Group $group): RedirectResponse
    {
        DB::transaction(function () use ($group) {
            GroupInvitation::where('group_id', $group->id)->delete();
            $group->delete();
        });

        return redirect()->route('admin.groups')->with('status', 'Group deleted successfully.');
    }

    public function showGroup(Group $group): View
    {
        $group->load([
            'creator',
            'members',
            'expenses.paidByUser',
            'settlements.fromUser',
            'settlements.toUser',
        ]);

        $snapshot = $this->balanceService->calculateSnapshot($group);
        $statements = $group->statementRecords()
            ->with('user')
            ->latest('transaction_date')
            ->get();

        return view('admin.group-detail', [
            'group' => $group,
            'snapshot' => $snapshot,
            'statements' => $statements,
        ]);
    }

    public function groupRecords(Group $group): View
    {
        $group->load([
            'creator',
            'members' => fn ($query) => $query->wherePivot('is_active', true)->orderBy('name'),
            'expenses' => fn ($query) => $query->latest('expense_date')->latest('created_at'),
            'expenses.paidByUser',
            'settlements' => fn ($query) => $query->latest('settlement_date')->latest('created_at'),
            'settlements.fromUser',
            'settlements.toUser',
        ]);

        $snapshot = $this->balanceService->calculateSnapshot($group);
        $statements = $group->statementRecords()
            ->with('user')
            ->latest('transaction_date')
            ->get();

        return view('admin.group-records', [
            'group' => $group,
            'snapshot' => $snapshot,
            'statements' => $statements,
        ]);
    }

    public function apiDocs(): View
    {
        $baseUrl = rtrim(url('/api/v1'), '/');
        $endpoints = [
            ['method' => 'POST', 'uri' => "{$baseUrl}/auth/register", 'description' => 'Create an iOS user account'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/auth/login", 'description' => 'Issue a Sanctum token for the iOS app'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/auth/google", 'description' => 'Login/register using Google ID token'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/auth/password/send-code", 'description' => 'Send one-time password reset code to email'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/auth/password/reset", 'description' => 'Reset password using email + one-time code'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups", 'description' => 'List authenticated user groups'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups", 'description' => 'Create a group'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups/join", 'description' => 'Join a group with invite code'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups/join/qr", 'description' => 'Join a group by scanning QR token'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/join-qr", 'description' => 'Get creator-only QR payload for group join'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/members", 'description' => 'List active members'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/expenses", 'description' => 'List group expenses'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups/{group}/expenses", 'description' => 'Create an expense'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups/{group}/expenses/{expense}/receipt", 'description' => 'Upload a receipt'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/settlements", 'description' => 'List settlements'],
            ['method' => 'POST', 'uri' => "{$baseUrl}/groups/{group}/settlements", 'description' => 'Create a settlement'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/balance", 'description' => 'Current balance snapshot'],
            ['method' => 'GET', 'uri' => "{$baseUrl}/groups/{group}/statements", 'description' => 'Statement feed'],
        ];

        return view('admin.api-docs', compact('baseUrl', 'endpoints'));
    }
}
