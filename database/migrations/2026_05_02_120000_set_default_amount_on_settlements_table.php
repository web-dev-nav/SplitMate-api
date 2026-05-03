<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settlements') || !Schema::hasColumn('settlements', 'amount')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Ensure existing rows are non-null before enforcing NOT NULL + default.
        DB::statement('UPDATE settlements SET amount = 0.00 WHERE amount IS NULL');
        DB::statement('ALTER TABLE settlements MODIFY amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    public function down(): void
    {
        if (!Schema::hasTable('settlements') || !Schema::hasColumn('settlements', 'amount')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE settlements MODIFY amount DECIMAL(10,2) NOT NULL');
    }
};
