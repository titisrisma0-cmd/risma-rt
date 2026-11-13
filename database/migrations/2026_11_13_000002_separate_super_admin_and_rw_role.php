<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memisahkan dengan tegas role SUPER ADMIN (khusus TITIS RISMAWATI)
     * dan role RW (Pengurus RW / Admin RW).
     */
    public function up(): void
    {
        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $rwRole = Role::where('nama_role', 'rw')->first() ?? Role::where('nama_role', 'admin')->first();

        // 1. Akun TITIS RISMAWATI adalah satu-satunya SUPER ADMIN
        DB::table('users')
            ->where('email', 'titisrisma0@gmail.com')
            ->update([
                'is_super_admin' => 1,
                'role_id' => $superAdminRole->id,
            ]);

        DB::table('user_profiles')
            ->whereIn('user_id', function ($query) {
                $query->select('id')->from('users')->where('email', 'titisrisma0@gmail.com');
            })
            ->update(['role_id' => $superAdminRole->id]);

        // 2. Akun Admin RW (admin@rw-smart.test, admin@risma-rt.test) adalah MURNI ROLE RW
        if ($rwRole) {
            DB::table('users')
                ->where('email', '!=', 'titisrisma0@gmail.com')
                ->whereIn('email', ['admin@rw-smart.test', 'admin@risma-rt.test'])
                ->update([
                    'is_super_admin' => 0,
                    'role_id' => $rwRole->id,
                ]);

            DB::table('user_profiles')
                ->whereIn('user_id', function ($query) {
                    $query->select('id')->from('users')
                        ->where('email', '!=', 'titisrisma0@gmail.com')
                        ->whereIn('email', ['admin@rw-smart.test', 'admin@risma-rt.test']);
                })
                ->update(['role_id' => $rwRole->id]);
        }

        // 3. Pastikan tidak ada user lain yang tidak sengaja memiliki is_super_admin = 1
        DB::table('users')
            ->where('email', '!=', 'titisrisma0@gmail.com')
            ->update(['is_super_admin' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
