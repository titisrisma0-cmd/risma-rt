<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->text('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 20)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('nik');
            }
            if (!Schema::hasColumn('users', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('users', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('users', 'agama')) {
                $table->string('agama')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('users', 'pekerjaan')) {
                $table->string('pekerjaan')->nullable()->after('agama');
            }
            if (!Schema::hasColumn('users', 'status_perkawinan')) {
                $table->string('status_perkawinan')->nullable()->after('pekerjaan');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 15)->nullable()->after('status_perkawinan');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'rt')) {
                $table->string('rt', 5)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'rw')) {
                $table->string('rw', 5)->nullable()->after('rt');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('rw');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'nik', 'tempat_lahir', 'tanggal_lahir', 
                'jenis_kelamin', 'agama', 'pekerjaan', 'status_perkawinan',
                'phone', 'address', 'rt', 'rw', 'bio'
            ]);
        });
    }
};