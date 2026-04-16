<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
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
}
