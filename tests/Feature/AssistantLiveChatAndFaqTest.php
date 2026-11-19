<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\AssistantFaq;
use App\Models\AssistantUnansweredQuestion;
use App\Models\KartuKeluarga;
use App\Models\Pembayaran;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SaldoSampah;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\AssistantService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssistantLiveChatAndFaqTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $adminRw;
    protected User $adminRt;
    protected User $wargaUserA;
    protected User $wargaUserB;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nama_role')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->boolean('is_super_admin')->default(false);
                $table->rememberToken();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
            });
        }

        if (!Schema::hasTable('rws')) {
            Schema::create('rws', function (Blueprint $table) {
                $table->id();
                $table->string('kode_rw')->unique();
                $table->string('nama_rw')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rts')) {
            Schema::create('rts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->string('kode_rt')->nullable();
                $table->string('nama_rt')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk')->unique();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('kepala_keluarga')->nullable();
                $table->text('alamat')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->string('nik')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('assistant_faqs')) {
            Schema::create('assistant_faqs', function (Blueprint $table) {
                $table->id();
                $table->string('pertanyaan');
                $table->text('kata_kunci')->nullable();
                $table->text('jawaban');
                $table->string('kategori')->default('Umum');
                $table->integer('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('perlu_tinjauan')->default(false);
                $table->string('catatan_tinjauan')->nullable();
                $table->text('field_pengurus')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('assistant_faqs', function (Blueprint $table) {
                if (!Schema::hasColumn('assistant_faqs', 'perlu_tinjauan')) {
                    $table->boolean('perlu_tinjauan')->default(false);
                }
                if (!Schema::hasColumn('assistant_faqs', 'catatan_tinjauan')) {
                    $table->string('catatan_tinjauan')->nullable();
                }
                if (!Schema::hasColumn('assistant_faqs', 'field_pengurus')) {
                    $table->text('field_pengurus')->nullable();
                }
            });
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

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('jenis_surat_id')->nullable();
                $table->string('nomor_pengajuan')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('iuran_id')->nullable();
                $table->decimal('jumlah_bayar', 12, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saldo_sampahs')) {
            Schema::create('saldo_sampahs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique();
                $table->decimal('saldo', 12, 2)->default(0);
                $table->decimal('total_berat_kg', 8, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('judul')->nullable();
                $table->text('pesan')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->string('tipe')->nullable();
                $table->string('link')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kegiatan')->nullable();
                $table->string('cakupan')->default('rw');
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jenis_surats')) {
            Schema::create('jenis_surats', function (Blueprint $table) {
                $table->id();
                $table->string('nama_surat')->nullable();
                $table->string('kode_surat')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->timestamps();
            });
        }

        $roleSuper = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $roleRw = Role::firstOrCreate(['nama_role' => 'rw']);
        $roleRt = Role::firstOrCreate(['nama_role' => 'rt']);
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        $rt01 = Rt::firstOrCreate(['kode_rt' => '01'], ['nama_rt' => 'RT 01']);
        $rt02 = Rt::firstOrCreate(['kode_rt' => '02'], ['nama_rt' => 'RT 02']);

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'sa.test.' . uniqid() . '@example.com'],
            ['name' => 'Super Admin Test', 'password' => bcrypt('password123'), 'role_id' => $roleSuper->id, 'is_super_admin' => true]
        );

        $this->adminRw = User::firstOrCreate(
            ['email' => 'rw.test.' . uniqid() . '@example.com'],
            ['name' => 'Admin RW Test', 'password' => bcrypt('password123'), 'role_id' => $roleRw->id, 'is_super_admin' => false]
        );

        $this->adminRt = User::firstOrCreate(
            ['email' => 'rt.test.' . uniqid() . '@example.com'],
            ['name' => 'Ketua RT 01 Test', 'password' => bcrypt('password123'), 'role_id' => $roleRt->id, 'rt_id' => $rt01->id, 'is_super_admin' => false]
        );

        // Warga A
        $this->wargaUserA = User::firstOrCreate(
            ['email' => 'warga.a.' . uniqid() . '@example.com'],
            ['name' => 'Budi Santoso', 'password' => bcrypt('password123'), 'role_id' => $roleWarga->id, 'rt_id' => $rt01->id, 'is_super_admin' => false]
        );
        $kkA = KartuKeluarga::firstOrCreate(['no_kk' => '3374011234560001'], ['rt_id' => $rt01->id, 'kepala_keluarga' => 'Budi Santoso']);
        UserProfile::updateOrCreate(
            ['user_id' => $this->wargaUserA->id],
            [
                'nama_lengkap' => 'Budi Santoso',
                'nik' => '3374010101900001',
                'kartu_keluarga_id' => $kkA->id,
                'rt_id' => $rt01->id,
                'status' => 'aktif'
            ]
        );

        // Warga B
        $this->wargaUserB = User::firstOrCreate(
            ['email' => 'warga.b.' . uniqid() . '@example.com'],
            ['name' => 'Siti Aminah', 'password' => bcrypt('password123'), 'role_id' => $roleWarga->id, 'rt_id' => $rt02->id, 'is_super_admin' => false]
        );
        $kkB = KartuKeluarga::firstOrCreate(['no_kk' => '3374021234560002'], ['rt_id' => $rt02->id, 'kepala_keluarga' => 'Siti Aminah']);
        UserProfile::updateOrCreate(
            ['user_id' => $this->wargaUserB->id],
            [
                'nama_lengkap' => 'Siti Aminah',
                'nik' => '3374020202950002',
                'kartu_keluarga_id' => $kkB->id,
                'rt_id' => $rt02->id,
                'status' => 'aktif'
            ]
        );

        // Seed Saldo Bank Sampah Warga A
        SaldoSampah::updateOrCreate(
            ['user_id' => $this->wargaUserA->id],
            ['saldo' => 75000, 'total_berat_kg' => 15.5]
        );

        // Seed Saldo Bank Sampah Warga B
        SaldoSampah::updateOrCreate(
            ['user_id' => $this->wargaUserB->id],
            ['saldo' => 120000, 'total_berat_kg' => 24.0]
        );

        // FAQ Dasar
        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Cara mendaftar akun warga'],
            [
                'kata_kunci' => 'cara mendaftar akun warga, cara daftar, daftar akun, pendaftaran warga, registrasi warga, buat akun warga, bikin akun, sign up warga, dftar akun, daftar',
                'jawaban' => 'Buka halaman login, pilih tab Pendaftaran Warga, pilih wilayah RT domisili, isi nama lengkap sesuai KTP, NIK, nomor KK, jenis kelamin, nomor WhatsApp, email, dan kata sandi, lalu klik Daftar Sebagai Warga.',
                'kategori' => 'Akun',
                'urutan' => 1,
                'is_active' => true,
                'perlu_tinjauan' => false,
            ]
        );

        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Lupa kata sandi'],
            [
                'kata_kunci' => 'lupa kata sandi, lupa sandi, lupa password, reset password, reset sandi, ganti password, ganti sandi, ubah password, lupa akun, ubah kata sandi',
                'jawaban' => 'Gunakan menu Lupa Password di halaman login, masukkan email Anda, lalu 6 digit kode verifikasi OTP akan dikirim lewat email untuk membuat kata sandi baru.',
                'kategori' => 'Akun',
                'urutan' => 2,
                'is_active' => true,
                'perlu_tinjauan' => false,
            ]
        );

        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Syarat surat pengantar'],
            [
                'kata_kunci' => 'syarat surat, surat pengantar, pengantar rt, skck, domisili, bikin surat',
                'jawaban' => 'Siapkan KTP, KK, dan bukti lunas iuran terbaru untuk membuat surat pengantar.',
                'kategori' => 'Layanan',
                'urutan' => 3,
                'is_active' => true,
                'perlu_tinjauan' => true,
            ]
        );

        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Cara bayar iuran'],
            [
                'kata_kunci' => 'cara bayar iuran, transfer iuran, qris, tagihan bulanan, bayar kas',
                'jawaban' => 'Halo {nama}, tagihan Anda saat ini: {tagihan_iuran}. Silakan masuk ke menu Keuangan > Pembayaran Iuran lalu transfer via Bank / QRIS.',
                'kategori' => 'Keuangan',
                'urutan' => 4,
                'is_active' => true,
                'perlu_tinjauan' => true,
            ]
        );

        // FAQ Posyandu (DRAFT)
        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Jadwal dan layanan Posyandu'],
            [
                'kata_kunci' => 'posyandu, jadwal posyandu, pos pelayanan terpadu, imunisasi, timbang balita, kia, kms, posyandu balita, posyandu lansia, layanan posyandu',
                'jawaban' => 'Posyandu RW 01 menyediakan pelayanan terpadu untuk ibu hamil, bayi, balita, dan lansia. Syarat: membawa buku KIA atau KMS.',
                'kategori' => 'Layanan Masyarakat',
                'urutan' => 7,
                'is_active' => false, // DRAFT
                'perlu_tinjauan' => true,
                'field_pengurus' => json_encode(['jadwal' => '', 'lokasi' => '', 'kader' => '']),
            ]
        );

        // FAQ Bank Sampah (DRAFT)
        AssistantFaq::updateOrCreate(
            ['pertanyaan' => 'Cara setor dan tabungan Bank Sampah'],
            [
                'kata_kunci' => 'bank sampah, setor sampah, tabungan sampah, tukar sampah, timbang sampah, daur ulang, cara setor bank sampah, jadwal bank sampah, tarik saldo sampah, saldo bank sampah',
                'jawaban' => 'Halo {nama}, saldo tabungan bank sampah Anda: {saldo_bank_sampah}. Sampah dipilah dan ditimbang menjadi saldo tabungan.',
                'kategori' => 'Layanan Masyarakat',
                'urutan' => 8,
                'is_active' => false, // DRAFT
                'perlu_tinjauan' => true,
                'field_pengurus' => json_encode(['jadwal_setor' => '', 'lokasi' => '', 'daftar_harga' => '']),
            ]
        );
    }

    /**
     * Requirement 1: Sapaan berdasarkan Login (Warga vs Tamu)
     */
    public function test_sapaan_berdasarkan_status_login(): void
    {
        // 1. Tamu (belum login) melihat sapaan umum dan tombol Masuk
        $responseGuest = $this->get(route('login'));
        $responseGuest->assertStatus(200);
        $responseGuest->assertSee('Halo! Saya Asisten Smart RW. Masuk dulu supaya saya bisa membantu lebih personal.');
        $responseGuest->assertSee('Masuk');

        // 2. Login sebagai Warga A (Budi Santoso) -> sapaan memuat nama depan "Budi"
        $responseUser = $this->actingAs($this->wargaUserA)->get(route('dashboard'));
        $responseUser->assertStatus(200);
        $responseUser->assertSee('Halo, Budi! Saya Asisten Smart RW. Ada yang bisa saya bantu?');

        // 3. Endpoint init API juga mengembalikan sapaan personal
        $resApiUser = $this->actingAs($this->wargaUserA)->getJson('/api/assistant/init');
        $resApiUser->assertStatus(200);
        $resApiUser->assertJson([
            'is_logged_in' => true,
            'first_name' => 'Budi',
        ]);
        $this->assertStringContainsString('Halo, Budi!', $resApiUser->json('greeting'));

        // Tamu pada endpoint API init
        auth()->logout();
        $resApiGuest = $this->getJson('/api/assistant/init');
        $resApiGuest->assertStatus(200);
        $resApiGuest->assertJson([
            'is_logged_in' => false,
        ]);
        $this->assertStringContainsString('Masuk dulu supaya saya bisa membantu lebih personal', $resApiGuest->json('greeting'));
    }

    /**
     * Requirement 1: Nama depan dengan karakter HTML disaring dari XSS
     */
    public function test_nama_dengan_karakter_html_disaring_dari_xss(): void
    {
        $xssUser = User::create([
            'name' => '<script>alert("hacked")</script> Joko',
            'email' => 'xss.' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        UserProfile::create([
            'user_id' => $xssUser->id,
            'nama_lengkap' => '<b>Joko</b> Susilo',
            'status' => 'aktif',
        ]);

        $firstName = AssistantService::getFirstName(optional($xssUser->profile)->nama_lengkap);
        $this->assertStringNotContainsString('<b>', $firstName);
        $this->assertEquals('Joko', $firstName);

        $response = $this->actingAs($xssUser)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('<b>Joko</b>', false);
    }

    /**
     * Requirement 1: Pertanyaan identitas ("kamu tau namaku ga", "siapa aku")
     */
    public function test_pertanyaan_identitas_warga_dan_tamu(): void
    {
        $service = new AssistantService();

        // 1. Saat login sebagai Warga A
        $resLogin = $service->ask('kamu tau namaku ga', null, $this->wargaUserA->id);
        $this->assertTrue($resLogin['matched']);
        $this->assertStringContainsString('Budi Santoso', $resLogin['reply']);
        $this->assertStringContainsString('Warga di RT 01 RW 01', $resLogin['reply']);

        // Variasi "siapa aku"
        $resSiapaAku = $service->ask('siapa aku', null, $this->wargaUserA->id);
        $this->assertTrue($resSiapaAku['matched']);
        $this->assertStringContainsString('Budi Santoso', $resSiapaAku['reply']);

        // 2. Saat tamu (belum login)
        $resTamu = $service->ask('kamu tahu namaku?');
        $this->assertTrue($resTamu['matched']);
        $this->assertStringContainsString('Anda belum masuk, jadi saya belum tahu siapa Anda. Silakan masuk dulu.', $resTamu['reply']);
        $this->assertTrue($resTamu['requires_login'] ?? false);
    }

    /**
     * Requirement 1: Chip saran cepat menyesuaikan peran (Warga, RT, RW)
     */
    public function test_saran_cepat_menyesuaikan_peran(): void
    {
        $chipsWarga = AssistantService::getSuggestionsForRole($this->wargaUserA);
        $chipsRt = AssistantService::getSuggestionsForRole($this->adminRt);
        $chipsRw = AssistantService::getSuggestionsForRole($this->adminRw);
        $chipsGuest = AssistantService::getSuggestionsForRole(null);

        // Warga punya chip spesifik warga
        $this->assertContains('Status surat saya', $chipsWarga);
        $this->assertContains('Cek saldo bank sampah', $chipsWarga);

        // RT punya chip verifikasi warga baru
        $this->assertContains('Verifikasi warga baru', $chipsRt);

        // RW punya chip rekap warga RW 01
        $this->assertContains('Rekap warga RW 01', $chipsRw);

        // Tamu punya chip cara daftar
        $this->assertContains('Cara mendaftar akun warga', $chipsGuest);
    }

    /**
     * Requirement: Setiap saran pertanyaan cepat pasti memiliki jawaban aktif dan tidak memicu fallback zonk
     */
    public function test_semua_saran_pertanyaan_cepat_pasti_memiliki_jawaban(): void
    {
        $service = new AssistantService();
        $rolesToTest = [
            'guest' => null,
            'warga' => $this->wargaUserA,
            'rt' => $this->adminRt,
            'rw' => $this->adminRw,
            'superadmin' => $this->superAdmin,
        ];

        foreach ($rolesToTest as $roleLabel => $user) {
            $chips = AssistantService::getSuggestionsForRole($user);
            $this->assertNotEmpty($chips, "Chips untuk {$roleLabel} tidak boleh kosong");

            foreach ($chips as $chip) {
                $response = $service->ask($chip, null, $user?->id);

                $this->assertTrue(
                    $response['matched'],
                    "Chip '{$chip}' pada role {$roleLabel} harus cocok dengan FAQ atau handler aktif"
                );

                $this->assertStringNotContainsString(
                    'belum menemukan jawabannya',
                    $response['reply'],
                    "Chip '{$chip}' pada role {$roleLabel} tidak boleh memicu pesan fallback zonk"
                );

                $this->assertNotEmpty($response['reply']);
            }
        }
    }

    /**
     * Requirement 2: Warga A tidak bisa melihat data Warga B lewat bot
     */
    public function test_warga_a_tidak_bisa_melihat_data_warga_b(): void
    {
        $service = new AssistantService();

        // Warga A mencoba melihat saldo bank sampah Warga B (Siti Aminah)
        $res1 = $service->ask('berapa saldo bank sampah siti?', null, $this->wargaUserA->id);
        $this->assertEquals(
            'Maaf, saya hanya bisa menampilkan data akun Anda sendiri.',
            $res1['reply']
        );

        // Warga A mencoba melihat data dengan NIK milik Warga B
        $res2 = $service->ask('tampilkan data dengan nik 3374020202950002', null, $this->wargaUserA->id);
        $this->assertEquals(
            'Maaf, saya hanya bisa menampilkan data akun Anda sendiri.',
            $res2['reply']
        );

        // Tamu mencoba meminta saldo bank sampah
        $resGuest = $service->ask('cek saldo bank sampah pak budi');
        $this->assertEquals(
            'Maaf, saya hanya bisa menampilkan data akun Anda sendiri.',
            $resGuest['reply']
        );
    }

    /**
     * Requirement 2: Warga A dapat melihat data pribadi miliknya sendiri dengan aman
     */
    public function test_warga_a_dapat_melihat_data_pribadi_sendiri(): void
    {
        $service = new AssistantService();

        // 1. Cek saldo bank sampah milik sendiri
        $resSaldo = $service->ask('cek saldo bank sampah saya', null, $this->wargaUserA->id);
        $this->assertTrue($resSaldo['matched']);
        $this->assertStringContainsString('Rp 75.000', $resSaldo['reply']);
        $this->assertStringContainsString('15,5 kg', $resSaldo['reply']);

        // 2. Cek NIK dan KK sendiri (disamarkan)
        $resNik = $service->ask('nik saya berapa', null, $this->wargaUserA->id);
        $this->assertTrue($resNik['matched']);
        $this->assertStringContainsString('3374********0001', $resNik['reply']);
        $this->assertStringContainsString('3374********0001', $resNik['reply']);
        // Tidak menampilkan password
        $this->assertStringNotContainsString('password', strtolower($resNik['reply']));
    }

    /**
     * Requirement 2: Placeholder {nama}, {rt}, {saldo_bank_sampah}, {tagihan_iuran} pada FAQ
     */
    public function test_faq_placeholders_replaced_with_user_data(): void
    {
        $service = new AssistantService();

        // Query yang cocok dengan FAQ "Cara bayar iuran"
        // Isi FAQ memiliki placeholder: "Halo {nama}, tagihan Anda saat ini: {tagihan_iuran}..."
        $resUser = $service->ask('cara bayar iuran', null, $this->wargaUserA->id);
        $this->assertTrue($resUser['matched']);
        $this->assertStringContainsString('Halo Budi', $resUser['reply']);
        $this->assertStringContainsString('tidak ada tagihan tertunggak', $resUser['reply']);

        // Jika ditanyakan oleh tamu: placeholder diganti "silakan cek di menu terkait"
        $resGuest = $service->ask('cara bayar iuran');
        $this->assertTrue($resGuest['matched']);
        $this->assertStringContainsString('silakan cek di menu terkait', $resGuest['reply']);
    }

    /**
     * Requirement 3: FAQ Layanan Masyarakat (Posyandu & Bank Sampah) saat DRAFT vs AKTIF
     */
    public function test_faq_layanan_masyarakat_draft_dan_aktif(): void
    {
        $service = new AssistantService();

        // 1. Saat DRAFT: ditanya "jadwal posyandu"
        $resPosyanduDraft = $service->ask('jadwal posyandu kapan');
        $this->assertFalse($resPosyanduDraft['matched']);
        $this->assertEquals(
            'Informasi ini belum diisi pengurus. Silakan tekan tombol CS di atas untuk bertanya langsung ke pengurus.',
            $resPosyanduDraft['reply']
        );
        // JANGAN menjawab dengan surat pengantar atau jadwal ronda!
        $this->assertStringNotContainsString('surat pengantar', strtolower($resPosyanduDraft['reply']));
        $this->assertStringNotContainsString('pos ronda', strtolower($resPosyanduDraft['reply']));

        // 2. Saat DRAFT: ditanya "cara setor bank sampah"
        $resSampahDraft = $service->ask('bagaimana cara setor bank sampah');
        $this->assertFalse($resSampahDraft['matched']);
        $this->assertEquals(
            'Informasi ini belum diisi pengurus. Silakan tekan tombol CS di atas untuk bertanya langsung ke pengurus.',
            $resSampahDraft['reply']
        );

        // 3. Saat FAQ diisi dan DIAKTIFKAN oleh pengurus
        $posyanduFaq = AssistantFaq::where('pertanyaan', 'Jadwal dan layanan Posyandu')->first();
        $posyanduFaq->update([
            'is_active' => true,
            'jawaban' => 'Posyandu RW 01 diadakan tiap hari Sabtu minggu pertama pukul 09.00 WIB di Balai RW 01.',
        ]);

        $resPosyanduAktif = $service->ask('jadwal posyandu kapan');
        $this->assertTrue($resPosyanduAktif['matched']);
        $this->assertStringContainsString('Balai RW 01', $resPosyanduAktif['reply']);
        $this->assertStringContainsString('Sabtu minggu pertama', $resPosyanduAktif['reply']);
    }

    /**
     * Requirement 1: Riwayat chat dan sapaan terisolasi antar akun (tidak bocor ke akun lain)
     */
    public function test_riwayat_chat_dan_identitas_terisolasi_antar_akun(): void
    {
        // Sesi Akun A (Budi Santoso)
        $resA = $this->actingAs($this->wargaUserA)->get(route('dashboard'));
        $resA->assertStatus(200);
        $resA->assertSee('data-current-user-id="' . $this->wargaUserA->id . '"', false);
        $resA->assertSee('data-user-name="Budi"', false);
        $resA->assertSee('Halo, Budi!');

        // Logout
        $this->post(route('logout'));

        // Sesi Tamu
        $resGuest = $this->get(route('login'));
        $resGuest->assertStatus(200);
        $resGuest->assertSee('data-current-user-id="guest"', false);
        $resGuest->assertSee('data-user-name=""', false);
        $resGuest->assertSee('Halo! Saya Asisten Smart RW. Masuk dulu supaya saya bisa membantu lebih personal.');

        // Sesi Akun B (Siti Aminah)
        $resB = $this->actingAs($this->wargaUserB)->get(route('dashboard'));
        $resB->assertStatus(200);
        $resB->assertSee('data-current-user-id="' . $this->wargaUserB->id . '"', false);
        $resB->assertSee('data-user-name="Siti"', false);
        $resB->assertSee('Halo, Siti!');
        // Tidak boleh ada nama Budi di sesi Siti
        $resB->assertDontSee('Halo, Budi!');
    }

    /**
     * Requirement 4: Halaman Kelola FAQ Asisten dikelola Super Admin dan RW
     */
    public function test_kelola_faq_page_access_control(): void
    {
        $resGuest = $this->get(route('assistant.faqs.index'));
        $resGuest->assertRedirect(route('login'));

        $resWarga = $this->actingAs($this->wargaUserA)->get(route('assistant.faqs.index'));
        $resWarga->assertStatus(403);

        $resSuper = $this->actingAs($this->superAdmin)->get(route('assistant.faqs.index'));
        $resSuper->assertStatus(200);
        $resSuper->assertSee('Kelola FAQ Asisten Smart RW');
        $resSuper->assertSee('Perlu Ditinjau Pengurus');

        $resRw = $this->actingAs($this->adminRw)->get(route('assistant.faqs.index'));
        $resRw->assertStatus(200);
    }

    /**
     * Requirement 4: CRUD FAQ dengan penanda tinjauan dan field_pengurus
     */
    public function test_superadmin_can_crud_faq_with_field_pengurus(): void
    {
        // 1. Create FAQ Layanan Masyarakat dengan field pengurus
        $resStore = $this->actingAs($this->superAdmin)->post(route('assistant.faqs.store'), [
            'pertanyaan' => 'Layanan Mobil Ambulans RW',
            'kata_kunci' => 'ambulans, mobil ambulans, darurat medis, sewa ambulans',
            'jawaban' => 'Layanan mobil ambulans siaga 24 jam untuk warga RW 01.',
            'kategori' => 'Layanan Masyarakat',
            'urutan' => 20,
            'is_active' => '1',
            'perlu_tinjauan' => '1',
            'catatan_tinjauan' => 'Perlu validasi kontak driver siaga',
            'field_pengurus' => '{"kontak_driver": "081234567890", "biaya": "Gratis untuk warga"}',
        ]);
        $resStore->assertRedirect(route('assistant.faqs.index'));
        $this->assertDatabaseHas('assistant_faqs', [
            'pertanyaan' => 'Layanan Mobil Ambulans RW',
            'perlu_tinjauan' => true,
        ]);

        $faq = AssistantFaq::where('pertanyaan', 'Layanan Mobil Ambulans RW')->first();

        // 2. Update FAQ
        $resUpdate = $this->actingAs($this->superAdmin)->put(route('assistant.faqs.update', $faq), [
            'pertanyaan' => 'Layanan Mobil Ambulans Siaga RW 01',
            'kata_kunci' => 'ambulans rw 01, mobil siaga',
            'jawaban' => 'Mobil ambulans siaga 24 jam gratis untuk rujukan darurat warga.',
            'kategori' => 'Layanan Masyarakat',
            'urutan' => 21,
            'is_active' => '1',
            'perlu_tinjauan' => '0',
            'catatan_tinjauan' => null,
            'field_pengurus' => '{"kontak_driver": "081234567899"}',
        ]);
        $resUpdate->assertRedirect(route('assistant.faqs.index'));
        $this->assertDatabaseHas('assistant_faqs', [
            'pertanyaan' => 'Layanan Mobil Ambulans Siaga RW 01',
            'perlu_tinjauan' => false,
        ]);

        // 3. Toggle Status FAQ
        $resToggle = $this->actingAs($this->superAdmin)->patch(route('assistant.faqs.toggle', $faq));
        $resToggle->assertRedirect(route('assistant.faqs.index'));
        $faq->refresh();
        $this->assertFalse($faq->is_active);

        // 4. Delete FAQ
        $resDelete = $this->actingAs($this->superAdmin)->delete(route('assistant.faqs.destroy', $faq));
        $resDelete->assertRedirect(route('assistant.faqs.index'));
        $this->assertDatabaseMissing('assistant_faqs', ['id' => $faq->id]);
    }

    /**
     * Rate Limiting: Endpoint API Chat dibatasi 20 pesan per jam per IP
     */
    public function test_api_assistant_chat_rate_limiting_enforces_20_per_hour(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $res = $this->postJson(route('api.assistant.chat'), [
                'message' => 'Pertanyaan ke-' . $i,
            ]);
            $res->assertStatus(200);
        }

        $resRateLimited = $this->postJson(route('api.assistant.chat'), [
            'message' => 'Pertanyaan melampaui batas kuota',
        ]);
        $resRateLimited->assertStatus(429);
    }
}
