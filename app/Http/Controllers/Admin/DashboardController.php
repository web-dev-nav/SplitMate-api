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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->limit(50)
            ->get();

        return view('admin.group-detail', [
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
