<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrasiNikKk16DigitTest extends TestCase
{
    use DatabaseTransactions;

    protected $rt;
    protected $roleWarga;

    protected function setUp(): void
    {
        parent::setUp();

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
                $table->rememberToken();
                $table->timestamps();
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
                $table->foreignId('rw_id');
                $table->string('kode_rt');
                $table->string('nama_rt')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk')->unique();
                $table->foreignId('rt_id');
                $table->foreignId('kepala_keluarga_user_id')->nullable();
                $table->text('alamat')->nullable();
                $table->string('status_rumah')->default('tetap');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->string('no_kk', 20)->nullable();
                $table->string('nik', 20)->nullable();
                $table->string('nama_lengkap');
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('agama')->nullable();
                $table->text('alamat')->nullable();
                $table->string('nomor_rumah')->nullable();
                $table->string('rt', 5)->nullable();
                $table->string('rw', 5)->nullable();
                $table->string('kelurahan')->nullable();
                $table->string('kecamatan')->nullable();
                $table->string('kota')->nullable();
                $table->string('no_hp', 25)->nullable();
                $table->string('pekerjaan')->nullable();
                $table->string('status_perkawinan')->nullable();
                $table->string('status_hubungan_keluarga')->nullable();
                $table->string('pendidikan')->nullable();
                $table->string('nama_ayah')->nullable();
                $table->string('nama_ibu')->nullable();
                $table->string('foto')->nullable();
                $table->string('foto_base64')->nullable();
                $table->string('status')->default('aktif');
                $table->boolean('siap_ronda')->default(true)->nullable();
                $table->string('alasan_pengecualian_ronda')->nullable();
                $table->text('catatan_verifikasi')->nullable();
                $table->foreignId('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('action')->nullable();
                $table->string('entity_type')->nullable();
                $table->foreignId('entity_id')->nullable();
                $table->text('deskripsi')->nullable();
                $table->decimal('nominal', 15, 2)->nullable();
                $table->string('bukti')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('from_user_id')->nullable();
                $table->string('tipe')->nullable();
                $table->string('judul')->nullable();
                $table->text('pesan')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }

        $rw = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01 Smart']);
        $this->rt = Rt::firstOrCreate(['rw_id' => $rw->id, 'kode_rt' => '01'], ['nama_rt' => 'RT 01']);
        $this->roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);
    }

    public function test_form_registrasi_menampilkan_atribut_input_16_digit(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Check NIK input attributes
        $response->assertSee('name="nik"', false);
        $response->assertSee('inputmode="numeric"', false);
        $response->assertSee('pattern="[0-9]{16}"', false);
        $response->assertSee('maxlength="16"', false);

        // Check No KK input attributes
        $response->assertSee('name="no_kk"', false);

        // Check script filter digits presence
        $response->assertSee('replace(/\D/g, \'\').slice(0, 16)', false);
    }

    public function test_pendaftaran_berhasil_dengan_nik_dan_no_kk_tepat_16_digit(): void
    {
        $nik = '320101' . rand(1000000000, 9999999999);
        $noKk = '320101' . rand(1000000000, 9999999999);
        $email = 'warga16_' . uniqid() . '@example.test';

        $response = $this->post(route('register'), [
            'name' => 'Warga Uji 16 Digit',
            'nik' => $nik,
            'rt_id' => $this->rt->id,
            'no_kk' => $noKk,
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $profile = UserProfile::where('nik', $nik)->first();
        $this->assertNotNull($profile);
        $this->assertEquals($nik, $profile->nik);
        $this->assertEquals($noKk, $profile->no_kk);
        $this->assertEquals(16, strlen($profile->nik));
        $this->assertEquals(16, strlen($profile->no_kk));
    }

    public function test_pendaftaran_gagal_jika_nik_hanya_15_digit(): void
    {
        $nik15 = '320101123456789'; // 15 digit
        $noKk = '320101' . rand(1000000000, 9999999999); // 16 digit
        $email = 'warga15nik_' . uniqid() . '@example.test';

        $response = $this->from('/login#register')->post(route('register'), [
            'name' => 'Warga NIK 15 Digit',
            'nik' => $nik15,
            'rt_id' => $this->rt->id,
            'no_kk' => $noKk,
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertStringContainsString('16 digit', session('errors')->first('nik'));
    }

    public function test_pendaftaran_gagal_jika_no_kk_hanya_15_digit(): void
    {
        $nik = '320101' . rand(1000000000, 9999999999); // 16 digit
        $noKk15 = '320101123456789'; // 15 digit
        $email = 'warga15kk_' . uniqid() . '@example.test';

        $response = $this->from('/login#register')->post(route('register'), [
            'name' => 'Warga KK 15 Digit',
            'nik' => $nik,
            'rt_id' => $this->rt->id,
            'no_kk' => $noKk15,
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['no_kk']);
        $this->assertStringContainsString('16 digit', session('errors')->first('no_kk'));
    }

    public function test_tipe_kolom_database_nik_dan_no_kk_adalah_string(): void
    {
        $nikType = Schema::getColumnType('user_profiles', 'nik');
        $noKkType = Schema::getColumnType('user_profiles', 'no_kk');
        $this->assertContains($nikType, ['string', 'varchar'], 'Kolom nik di user_profiles harus bertipe string/varchar');
        $this->assertContains($noKkType, ['string', 'varchar'], 'Kolom no_kk di user_profiles harus bertipe string/varchar');

        $kkNoType = Schema::getColumnType('kartu_keluargas', 'no_kk');
        $this->assertContains($kkNoType, ['string', 'varchar'], 'Kolom no_kk di kartu_keluargas harus bertipe string/varchar');
    }
}
