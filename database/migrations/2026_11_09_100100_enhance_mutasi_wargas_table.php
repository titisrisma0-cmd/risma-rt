<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mutasi_wargas')) {
            Schema::table('mutasi_wargas', function (Blueprint $table) {
                if (!Schema::hasColumn('mutasi_wargas', 'rt_id_tujuan')) {
                    $table->foreignId('rt_id_tujuan')->nullable()->after('rt_id')->constrained('rts')->nullOnDelete();
                }
                if (!Schema::hasColumn('mutasi_wargas', 'nama_kepala_keluarga')) {
                    $table->string('nama_kepala_keluarga')->nullable()->after('nama_warga');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'alamat_asal')) {
                    $table->text('alamat_asal')->nullable()->after('no_kk');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'alamat_tujuan')) {
                    $table->text('alamat_tujuan')->nullable()->after('alamat_asal');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'anggota_keluarga')) {
                    $table->json('anggota_keluarga')->nullable()->after('alamat_tujuan');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'dokumen_pendukung')) {
                    $table->string('dokumen_pendukung')->nullable()->after('anggota_keluarga');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'status_verifikasi')) {
                    $table->string('status_verifikasi', 50)->default('disetujui')->after('keterangan');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('mutasi_wargas', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('verified_by');
                }
                if (!Schema::hasColumn('mutasi_wargas', 'catatan_verifikasi')) {
                    $table->text('catatan_verifikasi')->nullable()->after('verified_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mutasi_wargas')) {
            Schema::table('mutasi_wargas', function (Blueprint $table) {
                $columns = [
                    'catatan_verifikasi', 'verified_at', 'verified_by', 'status_verifikasi',
                    'dokumen_pendukung', 'anggota_keluarga', 'alamat_tujuan', 'alamat_asal',
                    'nama_kepala_keluarga', 'rt_id_tujuan'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('mutasi_wargas', $column)) {
                        if (in_array($column, ['rt_id_tujuan', 'verified_by'])) {
                            $table->dropConstrainedForeignId($column);
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }
    }
};
