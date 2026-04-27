<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupUpdateOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_admin_can_edit_group_info(): void
    {
        $owner = $this->makeUser('owner@example.com');
        $admin = $this->makeUser('admin@example.com');
        $group = $this->makeGroup($owner);

        $group->members()->attach($owner->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
        $group->members()->attach($admin->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/groups/{$group->id}", [
            'name' => 'New Home Group',
            'currency_code' => 'cad',
        ]);

        $response->assertOk()
            ->assertJsonPath('group.name', 'New Home Group')
            ->assertJsonPath('group.currency_code', 'CAD');

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'New Home Group',
            'currency_code' => 'CAD',
        ]);
    }

    public function test_group_owner_can_transfer_ownership_to_active_member(): void
    {
        $owner = $this->makeUser('owner@example.com');
        $member = $this->makeUser('member@example.com');
        $group = $this->makeGroup($owner);

        $group->members()->attach($owner->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
        $group->members()->attach($member->id, ['role' => 'member', 'is_active' => true, 'joined_at' => now()]);

        Sanctum::actingAs($owner);

        $response = $this->patchJson("/api/v1/groups/{$group->id}", [
            'owner_user_id' => $member->uuid,
        ]);

        $response->assertOk()
            ->assertJsonPath('group.created_by_user_id', $member->uuid);

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'created_by_user_id' => $member->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'role' => 'admin',
            'is_active' => 1,
        ]);
    }

    public function test_non_owner_admin_cannot_transfer_ownership(): void
    {
        $owner = $this->makeUser('owner@example.com');
        $admin = $this->makeUser('admin@example.com');
        $group = $this->makeGroup($owner);

        $group->members()->attach($owner->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
        $group->members()->attach($admin->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/v1/groups/{$group->id}", [
            'owner_user_id' => $admin->uuid,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Only the current group owner can transfer ownership.');
    }

    private function makeUser(string $email): User
    {
        return User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'User '.Str::random(5),
            'email' => $email,
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function makeGroup(User $owner): Group
    {
        return Group::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Group',
            'invite_code' => strtoupper(Str::random(8)),
            'qr_join_token' => Str::random(48),
            'created_by_user_id' => $owner->id,
            'currency_code' => 'USD',
            'expense_categories' => Group::defaultExpenseCategories(),
        ]);
    }
}
