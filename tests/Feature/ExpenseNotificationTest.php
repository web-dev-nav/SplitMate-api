<?php

namespace Tests\Feature;

use App\Mail\ExpenseCreatedMail;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_email_is_sent_to_all_active_members_including_the_payer(): void
    {
        Mail::fake();

        $owner = $this->makeUser('owner@example.com');
        $member = $this->makeUser('member@example.com');
        $otherMember = $this->makeUser('other@example.com');

        $group = $this->makeGroup($owner, emailNotifications: true);
        $group->members()->attach($owner->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
        $group->members()->attach($member->id, ['role' => 'member', 'is_active' => true, 'joined_at' => now()]);
        $group->members()->attach($otherMember->id, ['role' => 'member', 'is_active' => true, 'joined_at' => now()]);

        Sanctum::actingAs($member);

        $response = $this->postJson("/api/v1/groups/{$group->id}/expenses", [
            'title' => 'Groceries',
            'amount_cents' => 3000,
            'paid_by_user_id' => $member->uuid,
            'expense_date' => now()->toDateString(),
            'category' => 'food',
            'participant_ids' => [
                $owner->uuid,
                $member->uuid,
                $otherMember->uuid,
            ],
        ]);

        $response->assertCreated();

        Mail::assertSent(ExpenseCreatedMail::class, 3);
        Mail::assertSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($owner->email));
        Mail::assertSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($member->email));
        Mail::assertSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($otherMember->email));
    }

    public function test_expense_email_respects_member_notification_preferences(): void
    {
        Mail::fake();

        $owner = $this->makeUser('owner2@example.com');
        $member = $this->makeUser('member2@example.com');
        $otherMember = $this->makeUser('other2@example.com');

        $group = $this->makeGroup($owner, emailNotifications: true);
        $group->members()->attach($owner->id, [
            'role' => 'admin',
            'is_active' => true,
            'expense_email_notifications' => true,
            'settlement_email_notifications' => true,
            'joined_at' => now(),
        ]);
        $group->members()->attach($member->id, [
            'role' => 'member',
            'is_active' => true,
            'expense_email_notifications' => false,
            'settlement_email_notifications' => true,
            'joined_at' => now(),
        ]);
        $group->members()->attach($otherMember->id, [
            'role' => 'member',
            'is_active' => true,
            'expense_email_notifications' => true,
            'settlement_email_notifications' => true,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson("/api/v1/groups/{$group->id}/expenses", [
            'title' => 'Groceries',
            'amount_cents' => 3000,
            'paid_by_user_id' => $member->uuid,
            'expense_date' => now()->toDateString(),
            'category' => 'food',
            'participant_ids' => [
                $owner->uuid,
                $member->uuid,
                $otherMember->uuid,
            ],
        ]);

        $response->assertCreated();

        Mail::assertSent(ExpenseCreatedMail::class, 2);
        Mail::assertSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($owner->email));
        Mail::assertNotSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($member->email));
        Mail::assertSent(ExpenseCreatedMail::class, fn (ExpenseCreatedMail $mail) => $mail->hasTo($otherMember->email));
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

    private function makeGroup(User $owner, bool $emailNotifications = false): Group
    {
        return Group::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Group',
            'invite_code' => strtoupper(Str::random(8)),
            'qr_join_token' => Str::random(48),
            'created_by_user_id' => $owner->id,
            'currency_code' => 'USD',
            'expense_categories' => Group::defaultExpenseCategories(),
            'email_notifications' => $emailNotifications,
        ]);
    }
}
