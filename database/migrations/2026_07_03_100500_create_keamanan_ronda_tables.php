<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontak_darurats', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori');
            $table->string('nomor_telepon', 30);
            $table->text('alamat')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('jadwal_rondas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->date('tanggal');
            $table->string('shift')->default('malam');
            $table->string('lokasi_pos')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('petugas_rondas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_ronda_id')->constrained('jadwal_rondas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('peran')->default('anggota');
            $table->timestamps();

            $table->unique(['jadwal_ronda_id', 'user_id']);
        });

        Schema::create('absensi_rondas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_ronda_id')->constrained('jadwal_rondas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'alpa'])->default('hadir');
            $table->timestamp('waktu_absen')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->decimal('lokasi_lat', 10, 7)->nullable();
            $table->decimal('lokasi_lng', 10, 7)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('panic_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('judul')->default('Panic Button');
            $table->text('deskripsi')->nullable();
            $table->decimal('lokasi_lat', 10, 7)->nullable();
            $table->decimal('lokasi_lng', 10, 7)->nullable();
            $table->enum('status', ['aktif', 'ditangani', 'selesai', 'false_alarm'])->default('aktif');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panic_reports');
        Schema::dropIfExists('absensi_rondas');
        Schema::dropIfExists('petugas_rondas');
        Schema::dropIfExists('jadwal_rondas');
        Schema::dropIfExists('kontak_darurats');
    }
};
