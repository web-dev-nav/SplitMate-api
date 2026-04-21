<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (!Schema::hasColumn('settlements', 'proof_photo')) {
                $table->string('proof_photo')->nullable()->after('amount_cents');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'proof_photo')) {
                $table->dropColumn('proof_photo');
            }
        });
    }
};
