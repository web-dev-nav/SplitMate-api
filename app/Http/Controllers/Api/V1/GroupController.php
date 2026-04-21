<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Support\ApiPayload;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Get all groups for the authenticated user.
     */
    public function index(Request $request)
    {
        $groups = $request->user()->groups()
            ->with('creator')
            ->get();

        return response()->json([
            'groups' => $groups->map(fn($group) => ApiPayload::group($group))->values(),
        ]);
    }

    /**
     * Create a new group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3',
        ]);

        $group = Group::create([
            'name' => $validated['name'],
            'invite_code' => Group::generateInviteCode(),
            'created_by_user_id' => $request->user()->id,
            'currency_code' => $validated['currency_code'],
        ]);

        // Add the creator as an admin member
        $group->members()->attach($request->user()->id, [
            'role' => 'admin',
            'is_active' => true,
        ]);
        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
            'invite_code' => $group->invite_code,
        ], 201);
    }

    /**
     * Get a specific group.
     */
    public function show(Request $request, Group $group)
    {
        // User must be a member of the group
        if (!$request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
        ]);
    }

    /**
     * Join a group using invite code.
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        if (!$request->user()->email_verified_at) {
            return response()->json([
                'message' => 'Verify your email before joining a group.',
            ], 403);
        }

        $group = Group::where('invite_code', $validated['invite_code'])->first();

        if (!$group) {
            return response()->json([
                'message' => 'Invalid invite code',
            ], 404);
        }

        // Check if user is already a member
        if ($request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'You are already a member of this group',
            ], 400);
        }

        // Add user to group
        $group->members()->attach($request->user()->id, [
            'role' => 'member',
            'is_active' => true,
        ]);
        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
            'message' => 'Successfully joined group',
        ], 200);
    }

    /**
     * Get members of a group.
     */
    public function members(Request $request, Group $group)
    {
        // User must be a member of the group
        if (!$request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $members = $group->members()
            ->wherePivot('is_active', true)
            ->get();

        return response()->json([
            'members' => $members->map(fn($user) => ApiPayload::groupMember($user))->values(),
        ]);
    }

    /**
     * Rename a group (admin only).
     */
    public function update(Request $request, Group $group)
    {
        if (!$this->isGroupAdmin($request, $group)) {
            return response()->json([
                'message' => 'Only group admins can update group settings.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group->update([
            'name' => $validated['name'],
        ]);
        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
            'message' => 'Group renamed successfully.',
        ]);
    }

    /**
     * Delete a group (admin only).
     */
    public function destroy(Request $request, Group $group)
    {
        if (!$this->isGroupAdmin($request, $group)) {
            return response()->json([
                'message' => 'Only group admins can delete this group.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully.',
        ]);
    }

    /**
     * Add an existing user to the group by verified email (admin only).
     */
    public function addMemberByEmail(Request $request, Group $group)
    {
        if (!$this->isGroupAdmin($request, $group)) {
            return response()->json([
                'message' => 'Only group admins can add members.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|string|email',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user) {
            return response()->json([
                'message' => 'No user found with that email. Ask them to register first.',
            ], 404);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'User email must be verified before joining the group.',
            ], 422);
        }

        if ($group->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is already a member of this group.',
            ], 400);
        }

        $group->members()->attach($user->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $member = $group->members()->where('users.id', $user->id)->firstOrFail();

        return response()->json([
            'member' => ApiPayload::groupMember($member),
            'message' => 'Member added successfully.',
        ], 201);
    }

    private function isGroupAdmin(Request $request, Group $group): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true)
            ->exists();
    }
}
