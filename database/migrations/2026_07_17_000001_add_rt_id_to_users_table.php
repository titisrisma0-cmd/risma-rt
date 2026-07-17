<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rt_id')->nullable()->after('role_id')->constrained('rts')->nullOnDelete();
        });

        DB::table('users')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->whereNotNull('user_profiles.rt_id')
            ->update(['users.rt_id' => DB::raw('user_profiles.rt_id')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rt_id');
        });
    }
};
