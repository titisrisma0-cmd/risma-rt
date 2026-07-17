<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'rw', 'rt', 'bendahara', 'warga'];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['nama_role' => $role]);
        }
    }
}
