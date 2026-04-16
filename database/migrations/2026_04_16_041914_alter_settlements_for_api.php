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
        Schema::table('settlements', function (Blueprint $table) {
            $table->string('uuid')->unique()->nullable();
            $table->string('group_id')->nullable();
            $table->bigInteger('amount_cents')->nullable();
        });

        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'payment_screenshot')) {
                $table->dropColumn('payment_screenshot');
            }
        });

        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'group_id')) {
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['group_id']);
            $table->dropColumn(['uuid', 'group_id', 'amount_cents']);
            $table->string('payment_screenshot')->nullable();
        });
    }
};
