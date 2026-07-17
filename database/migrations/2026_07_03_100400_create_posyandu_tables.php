<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kartu_keluarga_id')->nullable()->constrained('kartu_keluargas')->nullOnDelete();
            $table->foreignId('orang_tua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('nik', 20)->nullable()->unique();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('catatan_kesehatan')->nullable();
            $table->timestamps();
        });

        Schema::create('jadwal_posyandus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('judul');
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->string('lokasi');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pemeriksaan_balitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balitas')->cascadeOnDelete();
            $table->foreignId('jadwal_posyandu_id')->nullable()->constrained('jadwal_posyandus')->nullOnDelete();
            $table->date('tanggal_periksa');
            $table->decimal('berat_kg', 5, 2)->nullable();
            $table->decimal('tinggi_cm', 5, 2)->nullable();
            $table->decimal('lingkar_kepala_cm', 5, 2)->nullable();
            $table->json('imunisasi_sudah')->nullable();
            $table->json('imunisasi_belum')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_balitas');
        Schema::dropIfExists('jadwal_posyandus');
        Schema::dropIfExists('balitas');
    }
};
