<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'foto_base64')) {
                $table->longText('foto_base64')->nullable()->after('foto');
            }
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('foto_base64');
        });
    }
};