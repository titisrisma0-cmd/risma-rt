<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rw', 10)->unique();
            $table->string('nama_rw')->nullable();
            $table->foreignId('ketua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->constrained('rws')->cascadeOnDelete();
            $table->string('kode_rt', 10);
            $table->string('nama_rt')->nullable();
            $table->foreignId('ketua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['rw_id', 'kode_rt']);
        });

        Schema::create('kartu_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('no_kk', 20)->unique();
            $table->foreignId('kepala_keluarga_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('alamat');
            $table->enum('status_rumah', ['tetap', 'kontrak', 'kos', 'lainnya'])->default('tetap');
            $table->timestamps();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'rw_id')) {
                $table->foreignId('rw_id')->nullable()->after('role_id')->constrained('rws')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_profiles', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->after('rw_id')->constrained('rts')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_profiles', 'kartu_keluarga_id')) {
                $table->foreignId('kartu_keluarga_id')->nullable()->after('rt_id')->constrained('kartu_keluargas')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_profiles', 'nomor_rumah')) {
                $table->string('nomor_rumah', 20)->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('user_profiles', 'status_hubungan_keluarga')) {
                $table->string('status_hubungan_keluarga')->nullable()->after('status_perkawinan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            foreach (['kartu_keluarga_id', 'rt_id', 'rw_id'] as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            foreach (['nomor_rumah', 'status_hubungan_keluarga'] as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('kartu_keluargas');
        Schema::dropIfExists('rts');
        Schema::dropIfExists('rws');
    }
};
