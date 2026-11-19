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
                if (!Schema::hasColumn('assistant_faqs', 'field_pengurus')) {
                    $table->text('field_pengurus')->nullable()->after('catatan_tinjauan');
                }
            });

            // 1. FAQ: Posyandu (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Jadwal dan layanan Posyandu')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Jadwal dan layanan Posyandu',
                    'kata_kunci' => 'posyandu, jadwal posyandu, pos pelayanan terpadu, imunisasi, timbang balita, kia, kms, posyandu balita, posyandu lansia, layanan posyandu',
                    'jawaban' => 'Posyandu RW 01 menyediakan pelayanan terpadu untuk ibu hamil, bayi, balita, dan lansia (penimbangan berat badan, imunisasi, pemantauan tumbuh kembang, dan pemeriksaan dasar). Syarat: membawa buku KIA atau KMS.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 7,
                    'is_active' => false, // DRAFT sampai pengurus mengisi
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: jadwal, lokasi, kader penanggung jawab, dan syarat membawa buku KIA atau KMS.',
                    'field_pengurus' => json_encode([
                        'jadwal' => '',
                        'lokasi' => '',
                        'kader' => '',
                        'syarat' => 'Membawa buku KIA atau KMS'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. FAQ: Bank Sampah (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Cara setor dan tabungan Bank Sampah')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Cara setor dan tabungan Bank Sampah',
                    'kata_kunci' => 'bank sampah, setor sampah, tabungan sampah, tukar sampah, timbang sampah, daur ulang, cara setor bank sampah, jadwal bank sampah, tarik saldo sampah, saldo bank sampah',
                    'jawaban' => 'Sampah dipilah warga di rumah, lalu ditimbang dan dicatat sebagai saldo tabungan warga yang dapat ditarik atau dipakai pembayaran iuran.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 8,
                    'is_active' => false, // DRAFT sampai pengurus mengisi
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: jadwal setor, lokasi, daftar jenis sampah dan harga per kg, cara dan jadwal tarik saldo.',
                    'field_pengurus' => json_encode([
                        'jadwal_setor' => '',
                        'lokasi' => '',
                        'daftar_harga' => '',
                        'cara_tarik_saldo' => ''
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. FAQ: Kerja Bakti (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Kegiatan kerja bakti dan gotong royong')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Kegiatan kerja bakti dan gotong royong',
                    'kata_kunci' => 'kerja bakti, gotong royong, bersih lingkungan, jadwal kerja bakti, lokasi kerja bakti, kerja bakti rw',
                    'jawaban' => 'Kerja bakti dan gotong royong warga RW 01 dilaksanakan secara berkala untuk menjaga kebersihan dan kenyamanan lingkungan.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 9,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: jadwal dan lokasi kegiatan kerja bakti.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. FAQ: Kegiatan RW (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Agenda dan kegiatan rutin RW 01')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Agenda dan kegiatan rutin RW 01',
                    'kata_kunci' => 'kegiatan rw, agenda rw, acara rw, perkumpulan warga, senam rw, jadwal kegiatan rw',
                    'jawaban' => 'Agenda kegiatan rutin warga RW 01 meliputi pertemuan warga, senam sehat, dan perayaan hari besar lingkungan.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 10,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: agenda dan kalender kegiatan tahunan RW.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 5. FAQ: Pengaduan Lingkungan (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Cara menyampaikan pengaduan lingkungan')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Cara menyampaikan pengaduan lingkungan',
                    'kata_kunci' => 'pengaduan lingkungan, lapor keluhan, aduan warga, lapor fasilitas rusak, lapor masalah lingkungan',
                    'jawaban' => 'Warga dapat menyampaikan pengaduan atau keluhan seputar fasilitas dan lingkungan melalui menu Aspirasi & Pengaduan di aplikasi Smart RW atau menghubungi pengurus RT setempat.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 11,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: alur dan penanggung jawab tindak lanjut aduan lingkungan.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 6. FAQ: Layanan Lansia (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Program dan layanan kesehatan lansia')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Program dan layanan kesehatan lansia',
                    'kata_kunci' => 'layanan lansia, posyandu lansia, senam lansia, pemeriksaan lansia, lansia rw 01',
                    'jawaban' => 'Program lansia RW 01 mencakup posyandu lansia, senam kebugaran, dan pemeriksaan kesehatan rutin lansia.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 12,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: jadwal dan program khusus lansia RW.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 7. FAQ: Keamanan Lingkungan (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Sistem keamanan dan aturan portal lingkungan')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Sistem keamanan dan aturan portal lingkungan',
                    'kata_kunci' => 'keamanan lingkungan, portal gerbang, pos satpam, pos kamling, darurat keamanan, jam tutup portal',
                    'jawaban' => 'Keamanan wilayah RW 01 didukung oleh pos satpam, sistem ronda malam warga, dan pengawasan gerbang lingkungan.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 13,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: aturan jam buka/tutup portal dan prosedur tamu malam hari.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 8. FAQ: Kontak Darurat (DRAFT, Layanan Masyarakat)
            if (!DB::table('assistant_faqs')->where('pertanyaan', 'Nomor telepon dan kontak darurat RW 01')->exists()) {
                DB::table('assistant_faqs')->insert([
                    'pertanyaan' => 'Nomor telepon dan kontak darurat RW 01',
                    'kata_kunci' => 'kontak darurat, nomor darurat, telepon darurat, ambulans, pemadam kebakaran, bhabinkamtibmas, babinsa, polsek',
                    'jawaban' => 'Daftar nomor kontak darurat penting untuk penanganan cepat situasi darurat di wilayah RW 01.',
                    'kategori' => 'Layanan Masyarakat',
                    'urutan' => 14,
                    'is_active' => false,
                    'perlu_tinjauan' => true,
                    'catatan_tinjauan' => 'Perlu diisi pengurus: nomor telepon darurat valid instansi terkait.',
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
                if (Schema::hasColumn('assistant_faqs', 'field_pengurus')) {
                    $table->dropColumn('field_pengurus');
                }
            });
        }
    }
};
