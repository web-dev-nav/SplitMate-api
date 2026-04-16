<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
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
            'groups' => $groups->map(fn($group) => $this->formatGroup($group)),
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

        return response()->json([
            'group' => $this->formatGroup($group),
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

        return response()->json([
            'group' => $this->formatGroup($group),
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

        return response()->json([
            'group' => $this->formatGroup($group),
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
            'members' => $members->map(fn($user) => [
                'user_id' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->pivot->is_active,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->joined_at,
            ]),
        ]);
    }

    /**
     * Format a group for response.
     */
    private function formatGroup(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'invite_code' => $group->invite_code,
            'currency_code' => $group->currency_code,
            'created_by_user_id' => $group->created_by_user_id,
            'created_at' => $group->created_at,
            'updated_at' => $group->updated_at,
        ];
    }
}
