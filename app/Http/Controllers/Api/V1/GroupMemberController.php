<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    /**
     * Remove (deactivate) a member from a group (creator/admin only).
     */
    public function remove(Request $request, Group $group, User $user)
    {
        if (!$this->canManageMembers($request, $group)) {
            return response()->json([
                'message' => 'Only the group creator or admins can remove members.',
            ], 403);
        }

        if ((string) $group->created_by_user_id === (string) $user->id) {
            return response()->json([
                'message' => 'Group creator cannot be removed.',
            ], 400);
        }

        if ((string) $request->user()->id === (string) $user->id) {
            return response()->json([
                'message' => 'You cannot remove yourself from the group.',
            ], 400);
        }

        $targetMember = $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('is_active', true)
            ->exists();

        if (!$targetMember) {
            return response()->json([
                'message' => 'Member not found in this group.',
            ], 404);
        }

        $activeCount = $group->members()
            ->wherePivot('is_active', true)
            ->count();

        if ($activeCount <= 2) {
            return response()->json([
                'message' => 'Cannot remove member - minimum 2 active members required.',
            ], 400);
        }

        $group->members()->updateExistingPivot($user->id, [
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }

    /**
     * Deactivate current user in a group.
     */
    public function deactivate(Request $request, Group $group)
    {
        // User must be a member of the group
        if (!$request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get count of active members
        $activeCount = $group->members()
            ->wherePivot('is_active', true)
            ->count();

        // Ensure at least 2 active members remain
        if ($activeCount <= 2) {
            return response()->json([
                'message' => 'Cannot deactivate - minimum 2 active members required',
            ], 400);
        }

        // Deactivate the user in this group
        $group->members()->updateExistingPivot($request->user()->id, [
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Successfully deactivated from group',
        ]);
    }

    /**
     * Reactivate current user in a group.
     */
    public function reactivate(Request $request, Group $group)
    {
        // User must be a member of the group
        if (!$request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Reactivate the user in this group
        $group->members()->updateExistingPivot($request->user()->id, [
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Successfully reactivated in group',
        ]);
    }

    private function canManageMembers(Request $request, Group $group): bool
    {
        $actor = $request->user();
        if (!$actor) {
            return false;
        }

        if ((string) $group->created_by_user_id === (string) $actor->id) {
            return true;
        }

        return $group->members()
            ->where('users.id', $actor->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true)
            ->exists();
    }
}
