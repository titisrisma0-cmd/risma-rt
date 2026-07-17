<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['rw', 'rt'] as $role) {
            DB::table('roles')->updateOrInsert(['nama_role' => $role], ['updated_at' => now(), 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('nama_role', ['rw', 'rt'])->delete();
    }
};
