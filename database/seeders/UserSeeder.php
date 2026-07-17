<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $adminRole = Role::where('nama_role', 'admin')->first();
        $admin = User::create([
            'name' => 'Admin RW Smart',
            'email' => 'admin@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id
        ]);
        
        UserProfile::create([
            'user_id' => $admin->id,
            'role_id' => $adminRole->id,
            'no_kk' => '0000000000000000',
            'nik' => '0000000000000000',
            'nama_lengkap' => 'Admin RW Smart',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Contoh No. 1',
            'no_hp' => '081234567890',
            'pekerjaan' => 'Administrator',
            'status' => 'aktif'
        ]);
        
        // Bendahara
        $bendaharaRole = Role::where('nama_role', 'bendahara')->first();
        $bendahara = User::create([
            'name' => 'Bendahara RW',
            'email' => 'bendahara@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $bendaharaRole->id
        ]);
        
        UserProfile::create([
            'user_id' => $bendahara->id,
            'role_id' => $bendaharaRole->id,
            'no_kk' => '1111111111111111',
            'nik' => '1111111111111111',
            'nama_lengkap' => 'Bendahara RW',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1985-05-05',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Contoh No. 2',
            'no_hp' => '081234567891',
            'pekerjaan' => 'Ibu Rumah Tangga',
            'status' => 'aktif'
        ]);
        
        // Warga contoh
        $wargaRole = Role::where('nama_role', 'warga')->first();
        for ($i = 1; $i <= 5; $i++) {
            $warga = User::create([
                'name' => "Warga $i",
                'email' => "warga$i@rw-smart.test",
                'password' => Hash::make('password123'),
                'role_id' => $wargaRole->id
            ]);
            
            UserProfile::create([
                'user_id' => $warga->id,
                'role_id' => $wargaRole->id,
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
            ]);
        }
    }
}
