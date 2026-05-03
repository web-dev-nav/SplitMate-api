<?php

namespace Tests\Feature;

use App\Models\StatementRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatementRecordReferenceNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_number_does_not_reuse_deleted_sequence(): void
    {
        $user = $this->makeUser('ref-test@example.com');
        $prefix = 'EXP' . date('Ymd');

        $first = StatementRecord::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'transaction_type' => 'expense',
            'description' => 'First record',
            'amount' => 10.00,
            'amount_cents' => 1000,
            'reference_number' => $prefix . '000001',
            'balance_before' => 0,
            'balance_after' => 10,
            'balance_change' => 10,
            'balance_before_cents' => 0,
            'balance_after_cents' => 1000,
            'balance_change_cents' => 1000,
            'transaction_details' => ['note' => ''],
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        StatementRecord::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'transaction_type' => 'expense',
            'description' => 'Second record',
            'amount' => 20.00,
            'amount_cents' => 2000,
            'reference_number' => $prefix . '000002',
            'balance_before' => 10,
            'balance_after' => 30,
            'balance_change' => 20,
            'balance_before_cents' => 1000,
            'balance_after_cents' => 3000,
            'balance_change_cents' => 2000,
            'transaction_details' => ['note' => ''],
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        $first->delete();

        $nextReference = StatementRecord::generateReferenceNumber('EXP');

        $this->assertSame($prefix . '000003', $nextReference);
    }

    private function makeUser(string $email): User
    {
        return User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'User ' . Str::random(5),
            'email' => $email,
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
