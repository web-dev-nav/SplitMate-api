<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->boolean('expense_email_notifications')->default(true)->after('is_active');
            $table->boolean('settlement_email_notifications')->default(true)->after('expense_email_notifications');
        });
    }

    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn(['expense_email_notifications', 'settlement_email_notifications']);
        });
    }
};
