<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use App\Models\Rt;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Pastikan role utama tersedia
        $adminRole = Role::firstOrCreate(['nama_role' => 'admin']);
        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $bendaharaRole = Role::firstOrCreate(['nama_role' => 'bendahara']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);
        $genericRtRole = Role::firstOrCreate(['nama_role' => 'rt']);

        // 1. Akun Demo Admin / RW
        $admin = User::updateOrCreate(
            ['email' => 'admin@rw-smart.test'],
            [
                'name' => 'Admin RW Smart',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id
            ]
        );
        
        UserProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'role_id' => $adminRole->id,
                'no_kk' => '0000000000000000',
                'nik' => '0000000000000000',
                'nama_lengkap' => 'Admin RW Smart',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Contoh No. 1',
                'no_hp' => '081234567890',
                'pekerjaan' => 'Administrator RW',
                'status' => 'aktif'
            ]
        );
        
        // 2. Akun Demo Bendahara
        $bendahara = User::updateOrCreate(
            ['email' => 'bendahara@rw-smart.test'],
            [
                'name' => 'Bendahara RW',
                'password' => Hash::make('password123'),
                'role_id' => $bendaharaRole->id
            ]
        );
        
        UserProfile::updateOrCreate(
            ['user_id' => $bendahara->id],
            [
                'role_id' => $bendaharaRole->id,
                'no_kk' => '1111111111111111',
                'nik' => '1111111111111111',
                'nama_lengkap' => 'Bendahara RW',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1985-05-05',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Contoh No. 2',
                'no_hp' => '081234567891',
                'pekerjaan' => 'Bendahara RW',
                'status' => 'aktif'
            ]
        );

        // 3. Akun Demo Ketua RT 01 sampai RT 08
        for ($i = 1; $i <= 8; $i++) {
            $kodeRt = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $roleRt = Role::firstOrCreate(['nama_role' => 'rt_' . $kodeRt]);
            $rt = Rt::firstOrCreate(
                ['kode_rt' => $kodeRt],
                ['rw_id' => 1, 'nama_rt' => 'RT ' . $kodeRt]
            );

            $ketua = User::updateOrCreate(
                ['email' => "rt{$kodeRt}@rw-smart.test"],
                [
                    'name' => "Ketua RT {$kodeRt}",
                    'password' => Hash::make('password123'),
                    'role_id' => $roleRt->id,
                    'rt_id' => $rt->id,
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $ketua->id],
                [
                    'role_id' => $roleRt->id,
                    'rt_id' => $rt->id,
                    'rw_id' => 1,
                    'no_kk' => "10000000000000{$kodeRt}",
                    'nik' => "20000000000000{$kodeRt}",
                    'nama_lengkap' => "Ketua RT {$kodeRt}",
                    'tempat_lahir' => 'Jakarta',
                    'tanggal_lahir' => '1980-01-01',
                    'jenis_kelamin' => 'L',
                    'alamat' => "Jl. Wilayah RT {$kodeRt} No. 1",
                    'no_hp' => "0812345670{$kodeRt}",
                    'pekerjaan' => 'Ketua RT',
                    'status' => 'aktif',
                ]
            );

            $rt->update(['ketua_user_id' => $ketua->id]);
        }
        
        // 4. Akun Demo Warga
        $wargaDemo = User::updateOrCreate(
            ['email' => 'warga@rw-smart.test'],
            [
                'name' => 'Warga RW Smart',
                'password' => Hash::make('password123'),
                'role_id' => $wargaRole->id
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $wargaDemo->id],
            [
                'role_id' => $wargaRole->id,
                'rt_id' => 1,
                'no_kk' => '2222222222222200',
                'nik' => '3333333333333300',
                'nama_lengkap' => 'Warga RW Smart',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Contoh No. 0',
                'no_hp' => '081234567800',
                'pekerjaan' => 'Karyawan Swasta',
                'status' => 'aktif'
            ]
        );

        // Warga contoh tambahan (warga 1 sampai 5)
        for ($i = 1; $i <= 5; $i++) {
            $warga = User::updateOrCreate(
                ['email' => "warga$i@rw-smart.test"],
                [
                    'name' => "Warga $i",
                    'password' => Hash::make('password123'),
                    'role_id' => $wargaRole->id
                ]
            );
            
            UserProfile::updateOrCreate(
                ['user_id' => $warga->id],
                [
                    'role_id' => $wargaRole->id,
                    'rt_id' => (($i - 1) % 8) + 1,
                    'no_kk' => "222222222222222$i",
                    'nik' => "333333333333333$i",
                    'nama_lengkap' => "Warga $i RW",
                    'tempat_lahir' => 'Jakarta',
                    'tanggal_lahir' => "199$i-0$i-0$i",
                    'jenis_kelamin' => $i % 2 == 0 ? 'P' : 'L',
                    'alamat' => "Jl. Contoh No. $i",
                    'no_hp' => "08123456789$i",
                    'pekerjaan' => $i % 2 == 0 ? 'Guru' : 'Karyawan',
                    'status' => 'aktif'
                ]
            );
        }
    }
}
