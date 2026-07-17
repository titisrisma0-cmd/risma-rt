<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('nama_lokasi');
            $table->text('stream_url')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'rusak'])->default('aktif');
            $table->timestamps();
            $table->index(['rt_id', 'status']);
        });

        Schema::create('cctv_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_id')->constrained('cctv_cameras')->cascadeOnDelete();
            $table->text('file_path');
            $table->date('tanggal_rekaman');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedInteger('durasi_detik');
            $table->decimal('ukuran_file_mb', 10, 2);
            $table->timestamp('created_at')->nullable();
            $table->index(['camera_id', 'tanggal_rekaman']);
            $table->index('tanggal_rekaman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctv_recordings');
        Schema::dropIfExists('cctv_cameras');
    }
};
