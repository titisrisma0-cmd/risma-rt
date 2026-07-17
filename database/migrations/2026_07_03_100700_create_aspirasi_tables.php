<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('kategori');
            $table->string('judul');
            $table->text('deskripsi');
            $table->boolean('anonim')->default(false);
            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('solusi')->nullable();
            $table->timestamps();
        });

        Schema::create('aspirasi_lampirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirasi_id')->constrained('aspirasis')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });

        Schema::create('aspirasi_komentars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirasi_id')->constrained('aspirasis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('komentar');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirasi_komentars');
        Schema::dropIfExists('aspirasi_lampirans');
        Schema::dropIfExists('aspirasis');
    }
};
