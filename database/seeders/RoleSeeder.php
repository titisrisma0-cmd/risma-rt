<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'admin',
            'rw',
            'bendahara',
            'warga',
            'rt',
            'rt_01',
            'rt_02',
            'rt_03',
            'rt_04',
            'rt_05',
            'rt_06',
            'rt_07',
            'rt_08',
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['nama_role' => $role]);
        }
    }
}
