<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaginationAndDropdownUiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminRw;
    protected User $adminRt;
    protected Rw $rw;
    protected Rt $rt;

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
                $table->boolean('is_super_admin')->default(false);
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
                $table->foreignId('rw_id')->nullable();
                $table->string('kode_rt')->nullable();
                $table->string('nama_rt')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_lengkap');
                $table->string('nik')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk', 20)->nullable();
                $table->foreignId('kepala_keluarga_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->text('alamat')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('judul');
                $table->text('pesan')->nullable();
                $table->string('tipe')->default('info');
                $table->string('link')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }

        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);

        $this->rw = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        $this->rt = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['nama_rt' => 'RT 01', 'rw_id' => $this->rw->id]
        );

        $this->adminRw = User::firstOrCreate(
            ['email' => 'admin-rw-ui-test@example.test'],
            [
                'name' => 'Admin RW Test',
                'password' => bcrypt('password'),
                'role_id' => $rwRole->id,
            ]
        );

        $this->adminRt = User::firstOrCreate(
            ['email' => 'admin-rt-ui-test@example.test'],
            [
                'name' => 'Admin RT Test',
                'password' => bcrypt('password'),
                'role_id' => $rtRole->id,
                'rt_id' => $this->rt->id,
            ]
        );
    }

    public function test_pagination_translation_and_locale_are_indonesian(): void
    {
        $this->assertEquals('id', app()->getLocale());
        $this->assertEquals('« Sebelumnya', __('pagination.previous'));
        $this->assertEquals('Berikutnya »', __('pagination.next'));

        // Cek kalimat ringkasan paginasi
        $showing = __('Showing');
        $to = __('to');
        $of = __('of');
        $results = __('results');

        $summary = "{$showing} 1 {$to} 15 {$of} 233 {$results}";
        $this->assertEquals('Menampilkan 1 sampai 15 dari 233 data', $summary);
    }

    public function test_paginator_renders_bootstrap_five_with_emerald_theme_classes(): void
    {
        // Buat dummy paginator 233 data dengan 15 item per halaman
        $items = collect(range(1, 15));
        $paginator = new LengthAwarePaginator($items, 233, 15, 1, [
            'path' => url('/warga'),
            'pageName' => 'page',
        ]);

        $rendered = $paginator->links()->toHtml();

        // 1. Memakai kelas Bootstrap 5
        $this->assertStringContainsString('pagination', $rendered);
        $this->assertStringContainsString('page-item', $rendered);
        $this->assertStringContainsString('page-link', $rendered);

        // 2. Berisi teks terjemahan bahasa Indonesia
        $this->assertStringContainsString('Menampilkan', $rendered);
        $this->assertStringContainsString('sampai', $rendered);
        $this->assertStringContainsString('dari', $rendered);
        $this->assertStringContainsString('233', $rendered);
        $this->assertStringContainsString('data', $rendered);
        $this->assertStringContainsString('« Sebelumnya', $rendered);
        $this->assertStringContainsString('Berikutnya »', $rendered);
    }

    public function test_manajemen_rt_table_action_dropdown_uses_popper_fixed_and_viewport_boundary(): void
    {
        $response = $this->actingAs($this->adminRw)->get(route('rts.index'));

        $response->assertStatus(200);

        // Pastikan dropdown tombol titik tiga memiliki data-bs-boundary="viewport" dan strategy fixed
        $content = $response->getContent();
        $this->assertStringContainsString('data-bs-boundary="viewport"', $content);
        $this->assertStringContainsString('data-bs-popper-config=\'{"strategy":"fixed"}\'', $content);
        $this->assertStringContainsString('dropdown-menu-end', $content);
        $this->assertStringContainsString('fa-ellipsis-v', $content);
    }

    public function test_css_constrains_pagination_svg_size_and_sets_dropdown_z_index(): void
    {
        $cssPath = public_path('css/theme-dashboard.css');
        $this->assertFileExists($cssPath);

        $cssContent = file_get_contents($cssPath);

        // Membatasi ukuran semua SVG di dalam paginasi (1em)
        $this->assertStringContainsString('.pagination svg', $cssContent);
        $this->assertStringContainsString('width: 1em !important;', $cssContent);
        $this->assertStringContainsString('height: 1em !important;', $cssContent);

        // Dropdown menu z-index 1080
        $this->assertStringContainsString('.dropdown-menu', $cssContent);
        $this->assertStringContainsString('z-index: 1080 !important;', $cssContent);
    }
}
