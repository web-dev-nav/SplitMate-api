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
        Schema::table('statement_records', function (Blueprint $table) {
            $table->string('uuid')->unique()->nullable();
            $table->string('group_id')->nullable();
            $table->bigInteger('amount_cents')->nullable();
            $table->bigInteger('balance_before_cents')->nullable();
            $table->bigInteger('balance_after_cents')->nullable();
            $table->bigInteger('balance_change_cents')->nullable();
        });

        Schema::table('statement_records', function (Blueprint $table) {
            if (Schema::hasColumn('statement_records', 'group_id')) {
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statement_records', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['group_id']);
            $table->dropColumn(['uuid', 'group_id', 'amount_cents', 'balance_before_cents', 'balance_after_cents', 'balance_change_cents']);
        });
    }
};
