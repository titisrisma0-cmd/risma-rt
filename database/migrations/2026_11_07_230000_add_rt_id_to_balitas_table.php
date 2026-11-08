<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('balitas')) {
            Schema::table('balitas', function (Blueprint $table) {
                if (!Schema::hasColumn('balitas', 'rt_id')) {
                    $table->foreignId('rt_id')->nullable()->after('kartu_keluarga_id')->constrained('rts')->nullOnDelete();
                }
            });

            // Backfill rt_id from kartu_keluargas
            try {
                DB::statement('
                    UPDATE balitas b 
                    JOIN kartu_keluargas kk ON b.kartu_keluarga_id = kk.id 
                    SET b.rt_id = kk.rt_id 
                    WHERE b.rt_id IS NULL AND kk.rt_id IS NOT NULL
                ');
            } catch (\Throwable $e) {}

            // Backfill rt_id from user_profiles
            try {
                DB::statement('
                    UPDATE balitas b 
                    JOIN user_profiles p ON b.orang_tua_user_id = p.user_id 
                    SET b.rt_id = p.rt_id 
                    WHERE b.rt_id IS NULL AND p.rt_id IS NOT NULL
                ');
            } catch (\Throwable $e) {}

            // Backfill rt_id from users
            try {
                DB::statement('
                    UPDATE balitas b 
                    JOIN users u ON b.orang_tua_user_id = u.id 
                    SET b.rt_id = u.rt_id 
                    WHERE b.rt_id IS NULL AND u.rt_id IS NOT NULL
                ');
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('balitas') && Schema::hasColumn('balitas', 'rt_id')) {
            Schema::table('balitas', function (Blueprint $table) {
                $table->dropForeign(['rt_id']);
                $table->dropColumn('rt_id');
            });
        }
    }
};
