<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom siap_ronda, alasan, catatan_verifikasi di user_profiles
        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('user_profiles', 'siap_ronda')) {
                    $table->boolean('siap_ronda')->default(true)->after('status');
                }
                if (!Schema::hasColumn('user_profiles', 'alasan_pengecualian_ronda')) {
                    $table->string('alasan_pengecualian_ronda')->nullable()->after('siap_ronda');
                }
                if (!Schema::hasColumn('user_profiles', 'catatan_verifikasi')) {
                    $table->text('catatan_verifikasi')->nullable()->after('alasan_pengecualian_ronda');
                }
                if (!Schema::hasColumn('user_profiles', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->after('catatan_verifikasi')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('user_profiles', 'verified_at')) {
                    $table->dateTime('verified_at')->nullable()->after('verified_by');
                }
            });

            // Ubah tipe status menjadi string agar mendukung 'pending', 'aktif', 'nonaktif', 'ditolak', 'pindah', 'meninggal'
            try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE user_profiles MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'aktif'");
                }
            } catch (\Throwable $e) {
                // Abaikan jika sudah mendukung
            }
        }

        // 2. Hubungkan petugas_rondas dengan user_profiles
        if (Schema::hasTable('petugas_rondas')) {
            Schema::table('petugas_rondas', function (Blueprint $table) {
                if (!Schema::hasColumn('petugas_rondas', 'user_profile_id')) {
                    $table->foreignId('user_profile_id')->nullable()->after('jadwal_ronda_id')->constrained('user_profiles')->cascadeOnDelete();
                }
            });

            // Jadikan user_id nullable di MySQL jika masih NOT NULL
            try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE petugas_rondas MODIFY COLUMN user_id BIGINT UNSIGNED NULL");
                }
            } catch (\Throwable $e) {
                // Ignore
            }

            // Backfill user_profile_id dari user_id yang ada
            try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement("UPDATE petugas_rondas pr JOIN user_profiles up ON up.user_id = pr.user_id SET pr.user_profile_id = up.id WHERE pr.user_profile_id IS NULL");
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        // 3. Hubungkan absensi_rondas dengan user_profiles
        if (Schema::hasTable('absensi_rondas')) {
            Schema::table('absensi_rondas', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_rondas', 'user_profile_id')) {
                    $table->foreignId('user_profile_id')->nullable()->after('jadwal_ronda_id')->constrained('user_profiles')->cascadeOnDelete();
                }
            });

            try {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE absensi_rondas MODIFY COLUMN user_id BIGINT UNSIGNED NULL");
                    DB::statement("UPDATE absensi_rondas ar JOIN user_profiles up ON up.user_id = ar.user_id SET ar.user_profile_id = up.id WHERE ar.user_profile_id IS NULL");
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        // 4. Tambah jam_mulai dan jam_selesai di jadwal_rondas
        if (Schema::hasTable('jadwal_rondas')) {
            Schema::table('jadwal_rondas', function (Blueprint $table) {
                if (!Schema::hasColumn('jadwal_rondas', 'jam_mulai')) {
                    $table->string('jam_mulai', 10)->nullable()->after('shift');
                }
                if (!Schema::hasColumn('jadwal_rondas', 'jam_selesai')) {
                    $table->string('jam_selesai', 10)->nullable()->after('jam_mulai');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jadwal_rondas')) {
            Schema::table('jadwal_rondas', function (Blueprint $table) {
                $table->dropColumn(['jam_mulai', 'jam_selesai']);
            });
        }

        if (Schema::hasTable('absensi_rondas')) {
            Schema::table('absensi_rondas', function (Blueprint $table) {
                $table->dropForeign(['user_profile_id']);
                $table->dropColumn(['user_profile_id']);
            });
        }

        if (Schema::hasTable('petugas_rondas')) {
            Schema::table('petugas_rondas', function (Blueprint $table) {
                $table->dropForeign(['user_profile_id']);
                $table->dropColumn(['user_profile_id']);
            });
        }

        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn(['siap_ronda', 'alasan_pengecualian_ronda', 'catatan_verifikasi', 'verified_by', 'verified_at']);
            });
        }
    }
};
