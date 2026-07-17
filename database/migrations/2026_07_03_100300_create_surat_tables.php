<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_surats', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->json('field_form')->nullable();
            $table->json('persyaratan')->nullable();
            $table->text('template')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('pengajuan_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->restrictOnDelete();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('nomor_pengajuan')->unique();
            $table->string('nomor_surat')->nullable()->unique();
            $table->text('keperluan');
            $table->json('data_form_json')->nullable();
            $table->json('berkas_pendukung')->nullable();
            $table->enum('status', ['pending', 'diproses', 'ditolak', 'siap_diambil', 'selesai'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->string('file_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surats');
        Schema::dropIfExists('jenis_surats');
    }
};
