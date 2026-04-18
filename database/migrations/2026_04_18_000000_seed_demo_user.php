<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@splitmate.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Demo User',
                'password' => Hash::make('demo1234'),
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        User::where('email', 'demo@splitmate.com')->delete();
    }
};
