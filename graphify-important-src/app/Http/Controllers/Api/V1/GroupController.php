<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
            'qr_join_token' => Group::generateQrJoinToken(),
            'created_by_user_id' => $request->user()->id,
            'currency_code' => $validated['currency_code'],
            'expense_categories' => Group::defaultExpenseCategories(),
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

        if ((string) $group->created_by_user_id === (string) $request->user()->id) {
            return response()->json([
                'message' => 'Group creator cannot join their own group using invite code.',
            ], 400);
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
     * Join a group using QR token.
     */
    public function joinByQr(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:24|max:64',
        ]);

        if (!$request->user()->email_verified_at) {
            return response()->json([
                'message' => 'Verify your email before joining a group.',
            ], 403);
        }

        $group = Group::where('qr_join_token', $validated['token'])->first();

        if (!$group) {
            return response()->json([
                'message' => 'Invalid QR code',
            ], 404);
        }

        if ((string) $group->created_by_user_id === (string) $request->user()->id) {
            return response()->json([
                'message' => 'Group creator cannot join their own group using QR code.',
            ], 400);
        }

        if ($request->user()->groups->contains($group)) {
            return response()->json([
                'message' => 'You are already a member of this group',
            ], 400);
        }

        $group->members()->attach($request->user()->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);
        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
            'message' => 'Successfully joined group via QR code',
        ], 200);
    }

    /**
     * Get creator-only QR join payload for a group.
     */
    public function qrJoinCode(Request $request, Group $group)
    {
        if (!$this->isGroupCreator($request, $group)) {
            return response()->json([
                'message' => 'Only the group creator can access this QR code.',
            ], 403);
        }

        if (!$group->qr_join_token) {
            $group->update([
                'qr_join_token' => Group::generateQrJoinToken(),
            ]);
            $group->refresh();
        }

        $token = (string) $group->qr_join_token;
        $payload = [
            'type' => 'splitmate_group_join',
            'group_id' => $group->id,
            'token' => $token,
        ];

        return response()->json([
            'group_id' => $group->id,
            'group_name' => $group->name,
            'token' => $token,
            'qr_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'join_endpoint' => url('/api/v1/groups/join/qr'),
            'deep_link' => "splitmate://join-group?token={$token}",
        ]);
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
     * Get categories configured for this group.
     */
    public function categories(Request $request, Group $group)
    {
        return response()->json([
            'categories' => $group->expense_categories ?? Group::defaultExpenseCategories(),
        ]);
    }

    /**
     * Update categories for this group (creator only).
     */
    public function updateCategories(Request $request, Group $group)
    {
        if (!$this->isGroupCreator($request, $group)) {
            return response()->json([
                'message' => 'Only the group creator can manage categories.',
            ], 403);
        }

        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'required|string|min:1|max:50',
        ]);

        $normalized = collect($validated['categories'])
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->all();

        $group->update([
            'expense_categories' => $normalized,
        ]);

        return response()->json([
            'categories' => $normalized,
            'message' => 'Categories updated successfully.',
        ]);
    }

    /**
     * Update group settings.
     */
    public function update(Request $request, Group $group)
    {
        if (!$this->isGroupAdmin($request, $group)) {
            return response()->json([
                'message' => 'Only group admins can update group settings.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'currency_code' => 'sometimes|required|string|size:3',
            'owner_user_id' => 'sometimes|required|string|exists:users,uuid',
            'email_notifications' => 'sometimes|boolean',
        ]);

        $updates = [];
        if (array_key_exists('name', $validated)) {
            $updates['name'] = $validated['name'];
        }
        if (array_key_exists('currency_code', $validated)) {
            $updates['currency_code'] = strtoupper($validated['currency_code']);
        }
        if (array_key_exists('email_notifications', $validated)) {
            $updates['email_notifications'] = (bool) $validated['email_notifications'];
        }

        if (array_key_exists('owner_user_id', $validated)) {
            if (!$this->isGroupCreator($request, $group)) {
                return response()->json([
                    'message' => 'Only the current group owner can transfer ownership.',
                ], 403);
            }

            $newOwner = User::where('uuid', $validated['owner_user_id'])->first();
            if (!$newOwner) {
                return response()->json([
                    'message' => 'Owner user not found.',
                ], 404);
            }

            $isMember = $group->members()
                ->where('users.id', $newOwner->id)
                ->wherePivot('is_active', true)
                ->exists();

            if (!$isMember) {
                return response()->json([
                    'message' => 'New owner must be an active member of the group.',
                ], 422);
            }

            $updates['created_by_user_id'] = $newOwner->id;
            $group->members()->updateExistingPivot($newOwner->id, [
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        if (!empty($updates)) {
            $group->update($updates);
        }

        $group->load('creator');

        return response()->json([
            'group' => ApiPayload::group($group),
            'message' => 'Group updated successfully.',
        ]);
    }

    /**
     * Delete a group (admin only).
     */
    public function destroy(Request $request, Group $group)
    {
        if (!$this->isGroupAdmin($request, $group)) {
            return response()->json([
                'message' => 'Only the group creator or an admin can delete this group.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully.',
        ]);
    }

    /**
     * Send email invitation to join a group (admin only).
     * User becomes a member only after clicking the invitation link.
     */
    public function addMemberByEmail(Request $request, Group $group)
    {
        if (!$this->isGroupMember($request, $group)) {
            return response()->json([
                'message' => 'Only active members of this group can invite new members.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|string|email',
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $existingMember = $group->members()
            ->whereRaw('LOWER(users.email) = ?', [$normalizedEmail])
            ->wherePivot('is_active', true)
            ->exists();

        if ($existingMember) {
            return response()->json([
                'message' => 'This email is already an active member of the group.',
            ], 400);
        }

        GroupInvitation::where('group_id', $group->id)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->whereNull('accepted_at')
            ->delete();

        $token = Str::random(64);

        GroupInvitation::create([
            'group_id' => $group->id,
            'invited_by_user_id' => $request->user()->id,
            'email' => $normalizedEmail,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        $acceptUrl = url('/api/v1/invitations/accept/'.$token);

        try {
            Mail::send('emails.group-invitation', [
                'groupName' => $group->name,
                'inviterName' => $request->user()->name,
                'acceptUrl' => $acceptUrl,
                'expiresAt' => now()->addDays(7),
            ], function ($message) use ($normalizedEmail, $group) {
                $message->to($normalizedEmail)->subject("Invitation to join {$group->name} on SplitMate");
            });
        } catch (\Throwable $e) {
            // Don't fail API in local/dev when SMTP is not configured.
        }

        return response()->json([
            'message' => 'Invitation email sent. Member will appear after accepting the email link.',
            'accept_url' => app()->environment('local') ? $acceptUrl : null,
        ], 202);
    }

    /**
     * Accept invitation from email link.
     */
    public function acceptInvitation(string $token): View
    {
        $invitation = GroupInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('group')
            ->first();

        if (!$invitation) {
            return view('invitations.accept-result', [
                'status' => 'error',
                'title' => 'Invitation Expired',
                'message' => 'This invitation link is invalid or has expired.',
                'groupName' => null,
            ]);
        }

        $email = strtolower(trim($invitation->email));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            $name = ucfirst(str_replace(['.', '_', '-'], ' ', explode('@', $email)[0] ?? 'Member'));
            $user = User::create([
                'uuid' => Str::uuid()->toString(),
                'name' => $name ?: 'Member',
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } elseif (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $alreadyMember = $invitation->group->members()->where('users.id', $user->id)->first();
        if (!$alreadyMember) {
            $invitation->group->members()->attach($user->id, [
                'role' => 'member',
                'is_active' => true,
                'joined_at' => now(),
            ]);
        } else {
            $invitation->group->members()->updateExistingPivot($user->id, [
                'is_active' => true,
            ]);
        }

        $invitation->update([
            'accepted_at' => now(),
        ]);

        return view('invitations.accept-result', [
            'status' => 'success',
            'title' => 'You Are In!',
            'message' => "Invitation accepted. You are now a member of {$invitation->group->name}.",
            'groupName' => $invitation->group->name,
        ]);
    }

    private function isGroupAdmin(Request $request, Group $group): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        // Creator can always manage the group, even if legacy pivot role data is inconsistent.
        if ((int) $group->created_by_user_id === (int) $user->id) {
            return true;
        }

        return $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true)
            ->exists();
    }

    private function isGroupMember(Request $request, Group $group): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('is_active', true)
            ->exists();
    }

    private function isGroupCreator(Request $request, Group $group): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return (string) $group->created_by_user_id === (string) $user->id;
    }
}
