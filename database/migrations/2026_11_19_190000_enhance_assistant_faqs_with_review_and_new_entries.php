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
        if (Schema::hasTable('assistant_faqs')) {
            Schema::table('assistant_faqs', function (Blueprint $table) {
                if (!Schema::hasColumn('assistant_faqs', 'perlu_tinjauan')) {
                    $table->boolean('perlu_tinjauan')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('assistant_faqs', 'catatan_tinjauan')) {
                    $table->string('catatan_tinjauan')->nullable()->after('perlu_tinjauan');
                }
            });

            // Tandai FAQ bawaan yang memiliki fakta spesifik RW sebagai perlu ditinjau pengurus
            DB::table('assistant_faqs')->where('pertanyaan', 'like', '%Jadwal ronda%')->update([
                'perlu_tinjauan' => true,
                'catatan_tinjauan' => 'Memuat fakta spesifik: jam ronda 22.00 WIB dan lokasi Pos Ronda RW 01 (perlu ditinjau pengurus).',
            ]);

            DB::table('assistant_faqs')->where('pertanyaan', 'like', '%Cara bayar iuran%')->update([
                'perlu_tinjauan' => true,
                'catatan_tinjauan' => 'Memuat fakta spesifik: kanal pembayaran transfer bank/QRIS (perlu ditinjau pengurus).',
            ]);

            DB::table('assistant_faqs')->where('pertanyaan', 'like', '%Syarat surat pengantar%')->update([
                'perlu_tinjauan' => true,
                'catatan_tinjauan' => 'Memuat fakta spesifik: syarat dokumen lampiran (perlu ditinjau pengurus).',
            ]);

            // Tambahkan FAQ: Cara mendaftar akun warga (Aktif)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Cara mendaftar akun warga')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Cara mendaftar akun warga',
                    'kata_kunci' => 'cara mendaftar akun warga, cara daftar, daftar akun, pendaftaran warga, registrasi warga, buat akun warga, bikin akun, sign up warga, dftar akun, daftar',
                    'jawaban' => 'Buka halaman login, pilih tab Pendaftaran Warga, pilih wilayah RT domisili, isi nama lengkap sesuai KTP, NIK, nomor KK, jenis kelamin, nomor WhatsApp, email, dan kata sandi, lalu klik Daftar Sebagai Warga. Setelah mendaftar, akun diverifikasi oleh Ketua RT sesuai data kependudukan wilayah.',
                    'kategori' => 'Akun',
                    'urutan' => 1,
                    'is_active' => true,
                    'perlu_tinjauan' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tambahkan FAQ: Lupa kata sandi (Aktif)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Lupa kata sandi')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Lupa kata sandi',
                    'kata_kunci' => 'lupa kata sandi, lupa sandi, lupa password, reset password, reset sandi, ganti password, ganti sandi, ubah password, lupa akun, ubah kata sandi',
                    'jawaban' => 'Gunakan menu Lupa Password di halaman login, masukkan email Anda, lalu 6 digit kode verifikasi OTP akan dikirim lewat email untuk membuat kata sandi baru.',
                    'kategori' => 'Akun',
                    'urutan' => 2,
                    'is_active' => true,
                    'perlu_tinjauan' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tambahkan FAQ: Syarat menjadi warga RW 01 sebagai DRAFT (is_active = false)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Syarat menjadi warga RW 01')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Syarat menjadi warga RW 01',
                    'kata_kunci' => 'syarat menjadi warga rw 01, syarat menjadi warga, syarat jadi warga, jadi warga, jadiwarga, warga baru, domisili rw 01, pindah ke rw 01',
                    'jawaban' => 'Perlu diisi pengurus sesuai aturan RW.',
                    'kategori' => 'Kependudukan',
                    'urutan' => 6,
                    'is_active' => false, // DRAFT
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus sesuai aturan RW.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assistant_faqs')) {
            Schema::table('assistant_faqs', function (Blueprint $table) {
                if (Schema::hasColumn('assistant_faqs', 'perlu_tinjauan')) {
                    $table->dropColumn('perlu_tinjauan');
                }
                if (Schema::hasColumn('assistant_faqs', 'catatan_tinjauan')) {
                    $table->dropColumn('catatan_tinjauan');
                }
            });
        }
    }
};
