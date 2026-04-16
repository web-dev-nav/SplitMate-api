<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('wallet_snapshots');
        Schema::dropIfExists('balance_states');
        Schema::dropIfExists('expense_paybacks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // These tables are dead and should not be restored
        // If needed, recreate from old migrations
    }
};
