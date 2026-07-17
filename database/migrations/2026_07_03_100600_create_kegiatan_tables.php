<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->foreignId('rw_id')->nullable()->constrained('rws')->nullOnDelete();
            $table->string('judul');
            $table->string('kategori');
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['draft', 'terbit', 'selesai', 'dibatalkan'])->default('terbit');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rsvp_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_kegiatans');
        Schema::dropIfExists('kegiatans');
    }
};
