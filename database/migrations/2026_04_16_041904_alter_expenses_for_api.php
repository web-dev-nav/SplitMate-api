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
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'uuid')) {
                $table->string('uuid')->unique()->nullable();
            }
            if (!Schema::hasColumn('expenses', 'group_id')) {
                $table->string('group_id')->nullable();
            }
            if (!Schema::hasColumn('expenses', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('expenses', 'amount_cents')) {
                $table->bigInteger('amount_cents')->nullable();
            }
            if (!Schema::hasColumn('expenses', 'category')) {
                $table->string('category')->default('other');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'payback_to_user_id')) {
                $table->dropForeign(['payback_to_user_id']);
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            $columnNames = array_column(Schema::getColumns('expenses'), 'name');

            if (in_array('is_payback', $columnNames)) {
                $table->dropColumn('is_payback');
            }
            if (in_array('payback_to_user_id', $columnNames)) {
                $table->dropColumn('payback_to_user_id');
            }
            if (in_array('payback_amount', $columnNames)) {
                $table->dropColumn('payback_amount');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'group_id')) {
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['group_id']);
            $table->dropColumn(['uuid', 'group_id', 'title', 'amount_cents', 'category']);

            // Restore dead columns
            $table->boolean('is_payback')->default(false);
            $table->foreignId('payback_to_user_id')->nullable();
            $table->decimal('payback_amount', 10, 2)->nullable();
        });
    }
};
