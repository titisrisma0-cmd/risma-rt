<?php

namespace Tests\Feature;

use App\Models\CctvCamera;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CctvHierarchyAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('roles', fn (Blueprint $t) => [$t->id(), $t->string('nama_role'), $t->timestamps()]);
        Schema::create('users', fn (Blueprint $t) => [$t->id(), $t->string('name'), $t->string('email'), $t->string('password'), $t->foreignId('role_id'), $t->foreignId('rt_id')->nullable(), $t->rememberToken(), $t->timestamps()]);
        Schema::create('user_profiles', fn (Blueprint $t) => [$t->id(), $t->foreignId('user_id'), $t->foreignId('role_id')->nullable(), $t->string('nama_lengkap'), $t->string('status'), $t->timestamps()]);
        Schema::create('rws', fn (Blueprint $t) => [$t->id(), $t->string('kode_rw'), $t->timestamps()]);
        Schema::create('rts', fn (Blueprint $t) => [$t->id(), $t->foreignId('rw_id'), $t->string('kode_rt'), $t->timestamps()]);
        Schema::create('cctv_cameras', fn (Blueprint $t) => [$t->id(), $t->foreignId('rt_id'), $t->string('nama_lokasi'), $t->text('stream_url')->nullable(), $t->string('status'), $t->timestamps()]);
    }

    public function test_admin_rt_only_sees_and_authorizes_its_cameras(): void
    {
        [$rtAdmin, , $cameraA, $cameraB] = $this->data();
        $this->actingAs($rtAdmin);
        $this->assertEquals([$cameraA->id], CctvCamera::pluck('id')->all());
        $this->assertTrue(Gate::allows('update', $cameraA));
        $this->assertFalse(Gate::allows('update', $cameraB));
    }

    public function test_admin_rw_sees_all_cameras(): void
    {
        [, $rwAdmin] = $this->data();
        $this->actingAs($rwAdmin);
        $this->assertCount(2, CctvCamera::all());
    }

    private function data(): array
    {
        $rwRole = Role::create(['nama_role' => 'rw']); $rtRole = Role::create(['nama_role' => 'rt']);
        $rw = Rw::create(['kode_rw' => '01']);
        $rtA = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01']); $rtB = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '02']);
        $rtAdmin = User::create(['name'=>'RT','email'=>'rt@test.local','password'=>Hash::make('x'),'role_id'=>$rtRole->id,'rt_id'=>$rtA->id]);
        $rwAdmin = User::create(['name'=>'RW','email'=>'rw@test.local','password'=>Hash::make('x'),'role_id'=>$rwRole->id]);
        $cameraA = CctvCamera::withoutGlobalScopes()->create(['rt_id'=>$rtA->id,'nama_lokasi'=>'A','status'=>'aktif']);
        $cameraB = CctvCamera::withoutGlobalScopes()->create(['rt_id'=>$rtB->id,'nama_lokasi'=>'B','status'=>'aktif']);
        return [$rtAdmin->fresh('role'), $rwAdmin->fresh('role'), $cameraA, $cameraB];
    }
}
