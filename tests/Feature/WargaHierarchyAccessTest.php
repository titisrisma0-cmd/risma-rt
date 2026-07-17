<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Warga;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WargaHierarchyAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table) {
            $table->id(); $table->string('nama_role')->unique(); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email')->unique();
            $table->string('password'); $table->foreignId('role_id')->nullable();
            $table->foreignId('rt_id')->nullable(); $table->rememberToken(); $table->timestamps();
        });
        Schema::create('rws', function (Blueprint $table) {
            $table->id(); $table->string('kode_rw')->unique(); $table->string('nama_rw')->nullable();
            $table->foreignId('ketua_user_id')->nullable(); $table->timestamps();
        });
        Schema::create('rts', function (Blueprint $table) {
            $table->id(); $table->foreignId('rw_id'); $table->string('kode_rt');
            $table->string('nama_rt')->nullable(); $table->foreignId('ketua_user_id')->nullable(); $table->timestamps();
        });
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id'); $table->foreignId('role_id')->nullable();
            $table->foreignId('rw_id')->nullable(); $table->foreignId('rt_id')->nullable();
            $table->string('nama_lengkap'); $table->string('nik')->nullable(); $table->string('no_kk')->nullable();
            $table->string('status')->default('aktif'); $table->timestamps();
        });
    }

    public function test_admin_rt_scope_only_returns_its_own_warga(): void
    {
        [$adminRtA, , $wargaA, $wargaB] = $this->hierarchy();
        $this->actingAs($adminRtA);

        $ids = Warga::whereHas('role', fn ($query) => $query->where('nama_role', 'warga'))->pluck('id');

        $this->assertTrue($ids->contains($wargaA->id));
        $this->assertFalse($ids->contains($wargaB->id));
    }

    public function test_admin_rt_gets_403_for_warga_from_another_rt(): void
    {
        [$adminRtA, , , $wargaB] = $this->hierarchy();

        $this->actingAs($adminRtA)->get(route('warga.edit', $wargaB->id))->assertForbidden();
    }

    public function test_admin_rw_can_access_warga_from_all_rts(): void
    {
        [, $adminRw, $wargaA, $wargaB] = $this->hierarchy();

        $this->actingAs($adminRw);
        $wargaA = Warga::withoutGlobalScopes()->with('profile')->findOrFail($wargaA->id);
        $wargaB = Warga::withoutGlobalScopes()->with('profile')->findOrFail($wargaB->id);

        $this->assertTrue(Gate::allows('update', $wargaA));
        $this->assertTrue(Gate::allows('update', $wargaB));
        $this->assertCount(2, Warga::whereHas('role', fn ($query) => $query->where('nama_role', 'warga'))->get());
    }

    private function hierarchy(): array
    {
        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);
        $rw = Rw::create(['kode_rw' => 'T'.uniqid()]);
        $rtA = Rt::create(['rw_id' => $rw->id, 'kode_rt' => 'A'.uniqid()]);
        $rtB = Rt::create(['rw_id' => $rw->id, 'kode_rt' => 'B'.uniqid()]);
        $adminRtA = $this->user('rt-a', $rtRole, $rtA->id);
        $adminRw = $this->user('rw', $rwRole);
        $wargaA = $this->user('warga-a', $wargaRole, $rtA->id);
        $wargaB = $this->user('warga-b', $wargaRole, $rtB->id);

        return [$adminRtA, $adminRw, $wargaA, $wargaB];
    }

    private function user(string $name, Role $role, ?int $rtId = null): User
    {
        $user = User::create([
            'name' => $name,
            'email' => uniqid($name).'@example.test',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'rt_id' => $rtId,
        ]);
        UserProfile::updateOrCreate(['user_id' => $user->id], [
            'role_id' => $role->id,
            'rt_id' => $rtId,
            'nama_lengkap' => $name,
            'status' => 'aktif',
        ]);

        return $user->fresh(['role', 'profile']);
    }
}
