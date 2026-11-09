<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('balitas')) {
            Schema::table('balitas', function (Blueprint $table) {
                if (!Schema::hasColumn('balitas', 'user_profile_id')) {
                    $table->foreignId('user_profile_id')->nullable()->after('kartu_keluarga_id')->constrained('user_profiles')->nullOnDelete();
                }
                if (!Schema::hasColumn('balitas', 'nama_orang_tua')) {
                    $table->string('nama_orang_tua', 255)->nullable()->after('jenis_kelamin');
                }
                if (!Schema::hasColumn('balitas', 'no_hp_orang_tua')) {
                    $table->string('no_hp_orang_tua', 50)->nullable()->after('nama_orang_tua');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('balitas')) {
            Schema::table('balitas', function (Blueprint $table) {
                if (Schema::hasColumn('balitas', 'user_profile_id')) {
                    $table->dropConstrainedForeignId('user_profile_id');
                }
                if (Schema::hasColumn('balitas', 'no_hp_orang_tua')) {
                    $table->dropColumn('no_hp_orang_tua');
                }
                if (Schema::hasColumn('balitas', 'nama_orang_tua')) {
                    $table->dropColumn('nama_orang_tua');
                }
            });
        }
    }
};
