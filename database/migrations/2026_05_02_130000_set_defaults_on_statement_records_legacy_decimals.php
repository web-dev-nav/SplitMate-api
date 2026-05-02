<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('statement_records')) {
            return;
        }

        if (Schema::hasColumn('statement_records', 'amount')) {
            DB::statement('ALTER TABLE statement_records MODIFY amount DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
        if (Schema::hasColumn('statement_records', 'balance_before')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_before DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
        if (Schema::hasColumn('statement_records', 'balance_after')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_after DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
        if (Schema::hasColumn('statement_records', 'balance_change')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_change DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('statement_records')) {
            return;
        }

        if (Schema::hasColumn('statement_records', 'amount')) {
            DB::statement('ALTER TABLE statement_records MODIFY amount DECIMAL(10,2) NOT NULL');
        }
        if (Schema::hasColumn('statement_records', 'balance_before')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_before DECIMAL(10,2) NOT NULL');
        }
        if (Schema::hasColumn('statement_records', 'balance_after')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_after DECIMAL(10,2) NOT NULL');
        }
        if (Schema::hasColumn('statement_records', 'balance_change')) {
            DB::statement('ALTER TABLE statement_records MODIFY balance_change DECIMAL(10,2) NOT NULL');
        }
    }
};
