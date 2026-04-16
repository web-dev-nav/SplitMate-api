<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGroupMember
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $group = $request->route('group');

        if (!$group) {
            return $next($request);
        }

        // Check if the authenticated user is a member of the group
        $user = $request->user();

        if (!$user || !$user->groups()->where('groups.id', $group->id)->exists()) {
            return response()->json([
                'message' => 'Unauthorized - not a member of this group',
            ], 403);
        }

        return $next($request);
    }
}
