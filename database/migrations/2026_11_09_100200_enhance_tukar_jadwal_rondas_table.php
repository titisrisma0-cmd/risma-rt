<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::table('tukar_jadwal_rondas', function (Blueprint $table) {
                if (!Schema::hasColumn('tukar_jadwal_rondas', 'jenis_pengajuan')) {
                    $table->string('jenis_pengajuan', 50)->default('tukar_rekan')->after('target_jadwal_ronda_id');
                }
                if (!Schema::hasColumn('tukar_jadwal_rondas', 'target_user_approved_at')) {
                    $table->timestamp('target_user_approved_at')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::table('tukar_jadwal_rondas', function (Blueprint $table) {
                if (Schema::hasColumn('tukar_jadwal_rondas', 'target_user_approved_at')) {
                    $table->dropColumn('target_user_approved_at');
                }
                if (Schema::hasColumn('tukar_jadwal_rondas', 'jenis_pengajuan')) {
                    $table->dropColumn('jenis_pengajuan');
                }
            });
        }
    }
};
