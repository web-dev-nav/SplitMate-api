<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'qr_join_token')) {
                $table->string('qr_join_token', 64)->nullable()->unique()->after('invite_code');
            }
        });

        $groupIds = DB::table('groups')
            ->whereNull('qr_join_token')
            ->pluck('id');

        foreach ($groupIds as $groupId) {
            do {
                $token = Str::random(48);
            } while (DB::table('groups')->where('qr_join_token', $token)->exists());

            DB::table('groups')
                ->where('id', $groupId)
                ->update([
                    'qr_join_token' => $token,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'qr_join_token')) {
                $table->dropUnique('groups_qr_join_token_unique');
                $table->dropColumn('qr_join_token');
            }
        });
    }
};
