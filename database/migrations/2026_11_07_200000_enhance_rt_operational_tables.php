<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make user_id nullable in user_profiles
        if (Schema::hasTable('user_profiles')) {
            try {
                DB::statement('ALTER TABLE user_profiles MODIFY user_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // In SQLite (testing), recreate or ignore
            }
        }

        // 2. Enhance umkms table
        if (Schema::hasTable('umkms')) {
            Schema::table('umkms', function (Blueprint $table) {
                if (!Schema::hasColumn('umkms', 'rt_id')) {
                    $table->foreignId('rt_id')->nullable()->after('user_id')->constrained('rts')->nullOnDelete();
                }
                if (!Schema::hasColumn('umkms', 'cakupan')) {
                    $table->enum('cakupan', ['rt', 'rw'])->default('rt')->after('rt_id');
                }
            });

            // Backfill rt_id for existing umkms from user profile
            try {
                DB::statement('UPDATE umkms u JOIN user_profiles p ON u.user_id = p.user_id SET u.rt_id = p.rt_id WHERE u.rt_id IS NULL AND p.rt_id IS NOT NULL');
            } catch (\Throwable $e) {
            }
        }

        // 3. Enhance jadwal_bank_sampahs table
        if (Schema::hasTable('jadwal_bank_sampahs')) {
            Schema::table('jadwal_bank_sampahs', function (Blueprint $table) {
                if (!Schema::hasColumn('jadwal_bank_sampahs', 'petugas_nama')) {
                    $table->string('petugas_nama')->nullable()->after('lokasi');
                }
                if (!Schema::hasColumn('jadwal_bank_sampahs', 'petugas_kontak')) {
                    $table->string('petugas_kontak', 30)->nullable()->after('petugas_nama');
                }
                if (!Schema::hasColumn('jadwal_bank_sampahs', 'status')) {
                    $table->enum('status', ['terjadwal', 'berlangsung', 'selesai', 'ditunda'])->default('terjadwal')->after('aktif');
                }
            });
        }

        // 4. Enhance jadwal_posyandus table
        if (Schema::hasTable('jadwal_posyandus')) {
            Schema::table('jadwal_posyandus', function (Blueprint $table) {
                if (!Schema::hasColumn('jadwal_posyandus', 'nama_bidan')) {
                    $table->string('nama_bidan')->nullable()->after('lokasi');
                }
                if (!Schema::hasColumn('jadwal_posyandus', 'kontak_bidan')) {
                    $table->string('kontak_bidan', 30)->nullable()->after('nama_bidan');
                }
                if (!Schema::hasColumn('jadwal_posyandus', 'kader_info')) {
                    $table->text('kader_info')->nullable()->after('kontak_bidan');
                }
                if (!Schema::hasColumn('jadwal_posyandus', 'status')) {
                    $table->enum('status', ['terjadwal', 'berlangsung', 'selesai', 'dibatalkan'])->default('terjadwal')->after('keterangan');
                }
            });
        }

        // 5. Enhance pemeriksaan_balitas table
        if (Schema::hasTable('pemeriksaan_balitas')) {
            Schema::table('pemeriksaan_balitas', function (Blueprint $table) {
                if (!Schema::hasColumn('pemeriksaan_balitas', 'lingkar_lengan_cm')) {
                    $table->decimal('lingkar_lengan_cm', 5, 2)->nullable()->after('lingkar_kepala_cm');
                }
                if (!Schema::hasColumn('pemeriksaan_balitas', 'status_gizi')) {
                    $table->string('status_gizi', 50)->nullable()->after('lingkar_lengan_cm');
                }
            });
        }

        // 6. Create tukar_jadwal_rondas table
        if (!Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::create('tukar_jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('jadwal_ronda_asal_id')->constrained('jadwal_rondas')->cascadeOnDelete();
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('target_jadwal_ronda_id')->nullable()->constrained('jadwal_rondas')->nullOnDelete();
                $table->text('alasan');
                $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
                $table->text('catatan_rt')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tukar_jadwal_rondas');

        if (Schema::hasTable('pemeriksaan_balitas')) {
            Schema::table('pemeriksaan_balitas', function (Blueprint $table) {
                $table->dropColumn(['lingkar_lengan_cm', 'status_gizi']);
            });
        }

        if (Schema::hasTable('jadwal_posyandus')) {
            Schema::table('jadwal_posyandus', function (Blueprint $table) {
                $table->dropColumn(['nama_bidan', 'kontak_bidan', 'kader_info', 'status']);
            });
        }

        if (Schema::hasTable('jadwal_bank_sampahs')) {
            Schema::table('jadwal_bank_sampahs', function (Blueprint $table) {
                $table->dropColumn(['petugas_nama', 'petugas_kontak', 'status']);
            });
        }

        if (Schema::hasTable('umkms')) {
            Schema::table('umkms', function (Blueprint $table) {
                $table->dropForeign(['rt_id']);
                $table->dropColumn(['rt_id', 'cakupan']);
            });
        }
    }
};
