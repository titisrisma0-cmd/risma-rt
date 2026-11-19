<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('assistant_faqs')) {
            Schema::create('assistant_faqs', function (Blueprint $table) {
                $table->id();
                $table->string('pertanyaan');
                $table->text('kata_kunci')->nullable();
                $table->text('jawaban');
                $table->string('kategori')->default('Umum');
                $table->integer('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });

            // Data Awal FAQ Bawaan (Syarat surat, Cara bayar, Jadwal ronda)
            DB::table('assistant_faqs')->insert([
                [
                    'pertanyaan' => 'Syarat surat pengantar',
                    'kata_kunci' => 'syarat surat pengantar, surat pengantar, pengantar rt, bikin surat, surat domisili, skck, pengantar rw',
                    'jawaban' => "Untuk membuat Surat Pengantar RT/RW, siapkan:\n1. KTP & Kartu Keluarga (KK).\n2. Bukti lunas iuran bulanan terbaru.\n3. Dokumen pendukung sesuai keperluan (SKCK, pengantar nikah, atau domisili).\n\nPengajuan dapat dilakukan online via menu 'Layanan Surat' di aplikasi atau langsung menghubungi Ketua RT setempat.",
                    'kategori' => 'Layanan',
                    'urutan' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'pertanyaan' => 'Cara bayar iuran',
                    'kata_kunci' => 'cara bayar iuran, bayar iuran, transfer iuran, qris, tagihan bulanan, bayar kas, biaya iuran',
                    'jawaban' => "Pembayaran iuran dapat dilakukan secara praktis:\n1. Masuk ke menu 'Keuangan' > 'Pembayaran Iuran'.\n2. Pilih tagihan bulan berjalan.\n3. Lakukan pembayaran via Transfer Bank / QRIS yang tertera, lalu unggah bukti transfer di formulir untuk diverifikasi pengurus.",
                    'kategori' => 'Keuangan',
                    'urutan' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'pertanyaan' => 'Jadwal ronda',
                    'kata_kunci' => 'jadwal ronda, ronda malam, pos ronda, giliran jaga, siskamling, tukar ronda',
                    'jawaban' => "Jadwal ronda malam dapat dilihat pada dashboard akun warga Anda di kartu 'Jadwal Ronda Saya' atau menu 'Keamanan / Ronda'.\n\nRonda dimulai pukul 22.00 WIB di Pos Ronda RW 01. Jika Anda berhalangan hadir, silakan ajukan 'Tukar Jadwal' kepada rekan ronda sebelum giliran jaga.",
                    'kategori' => 'Keamanan',
                    'urutan' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (!Schema::hasTable('assistant_unanswered_questions')) {
            Schema::create('assistant_unanswered_questions', function (Blueprint $table) {
                $table->id();
                $table->text('pertanyaan');
                $table->string('ip_address', 45)->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('status', 30)->default('belum_dijawab');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistant_unanswered_questions');
        Schema::dropIfExists('assistant_faqs');
    }
};
