<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. RT Financial Audits table
        if (!Schema::hasTable('rt_audits')) {
            Schema::create('rt_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
                $table->integer('tahun');
                $table->integer('bulan'); // 1-12
                $table->decimal('total_pemasukan', 15, 2)->default(0);
                $table->decimal('total_pengeluaran', 15, 2)->default(0);
                $table->decimal('saldo_kas', 15, 2)->default(0);
                $table->string('status')->default('belum_diperiksa'); // belum_diperiksa, sedang_diperiksa, perlu_perbaikan, terverifikasi, ditolak
                $table->text('catatan_rw')->nullable();
                $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('audited_at')->nullable();
                $table->timestamps();

                $table->unique(['rt_id', 'tahun', 'bulan']);
            });
        }

        // 2. Audit Logs table (Audit trail for RT and RW)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
                $table->string('action'); // e.g. 'iuran_create', 'pembayaran_verifikasi', 'pengeluaran_create', 'saldo_koreksi', 'audit_review'
                $table->string('entity_type'); // e.g. 'pembayaran', 'pengeluaran', 'kas', 'surat', 'audit'
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('deskripsi')->nullable();
                $table->decimal('nominal', 15, 2)->nullable();
                $table->string('bukti')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // 3. Mutasi Warga table
        if (!Schema::hasTable('mutasi_wargas')) {
            Schema::create('mutasi_wargas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
                $table->foreignId('user_profile_id')->nullable()->constrained('user_profiles')->nullOnDelete();
                $table->string('nama_warga');
                $table->string('nik', 30)->nullable();
                $table->string('no_kk', 30)->nullable();
                $table->string('jenis_mutasi'); // 'masuk', 'keluar', 'meninggal', 'pindah_rt', 'lainnya'
                $table->date('tanggal_mutasi');
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Update pengajuan_surats for escalation workflow
        if (Schema::hasTable('pengajuan_surats')) {
            Schema::table('pengajuan_surats', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuan_surats', 'target_level')) {
                    $table->string('target_level')->default('rt')->after('rt_id');
                }
                if (!Schema::hasColumn('pengajuan_surats', 'kebutuhan_rw')) {
                    $table->boolean('kebutuhan_rw')->default(false)->after('target_level');
                }
                if (!Schema::hasColumn('pengajuan_surats', 'catatan_rt')) {
                    $table->text('catatan_rt')->nullable()->after('catatan');
                }
                if (!Schema::hasColumn('pengajuan_surats', 'catatan_rw')) {
                    $table->text('catatan_rw')->nullable()->after('catatan_rt');
                }
                if (!Schema::hasColumn('pengajuan_surats', 'rt_approved_by')) {
                    $table->foreignId('rt_approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('pengajuan_surats', 'rt_approved_at')) {
                    $table->timestamp('rt_approved_at')->nullable()->after('rt_approved_by');
                }
                if (!Schema::hasColumn('pengajuan_surats', 'rw_approved_by')) {
                    $table->foreignId('rw_approved_by')->nullable()->after('rt_approved_at')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('pengajuan_surats', 'rw_approved_at')) {
                    $table->timestamp('rw_approved_at')->nullable()->after('rw_approved_by');
                }
            });

            // Modify status column to varchar(50) so it supports extended workflow statuses:
            // 'menunggu_rt', 'diproses_rt', 'menunggu_rw', 'disetujui', 'ditolak', 'selesai'
            try {
                DB::statement("ALTER TABLE pengajuan_surats MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'menunggu_rt'");
            } catch (\Throwable $e) {
                // If already modified or not supported by engine, ignore
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_wargas');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('rt_audits');
    }
};
