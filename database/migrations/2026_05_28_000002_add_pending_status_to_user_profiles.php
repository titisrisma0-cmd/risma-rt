<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_profiles', 'status') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE user_profiles MODIFY status ENUM('pending', 'aktif', 'nonaktif') NOT NULL DEFAULT 'aktif'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_profiles', 'status') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE user_profiles MODIFY status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif'");
        }
    }
};
