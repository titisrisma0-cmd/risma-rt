<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure 'super_admin' exists in roles table
        $superAdminRoleId = null;
        if (Schema::hasTable('roles')) {
            $existing = DB::table('roles')->where('nama_role', 'super_admin')->first();
            if ($existing) {
                $superAdminRoleId = $existing->id;
            } else {
                $superAdminRoleId = DB::table('roles')->insertGetId([
                    'nama_role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Add is_super_admin column to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('role_id')->index();
            });
        }

        // 3. Mark Titis Rismawati as Super Admin
        if (Schema::hasTable('users')) {
            // By email or name
            $updateData = ['is_super_admin' => true];
            if ($superAdminRoleId) {
                $updateData['role_id'] = $superAdminRoleId;
            }

            DB::table('users')
                ->where('email', 'titisrisma0@gmail.com')
                ->orWhere('name', 'Titis Rismawati')
                ->orWhere('email', 'admin@rw-smart.test')
                ->update($updateData);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_super_admin');
            });
        }
    }
};
