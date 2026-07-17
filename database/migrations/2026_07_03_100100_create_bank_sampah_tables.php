<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sampahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->decimal('harga_per_kg', 12, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('saldo_sampahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('total_berat_kg', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('setoran_sampahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampahs')->restrictOnDelete();
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('total_saldo', 15, 2);
            $table->enum('status', ['pending', 'valid', 'ditolak'])->default('valid');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('penarikan_saldo_sampahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->enum('metode', ['tunai', 'transfer'])->default('tunai');
            $table->string('rekening_tujuan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak', 'dibayar'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan_saldo_sampahs');
        Schema::dropIfExists('setoran_sampahs');
        Schema::dropIfExists('saldo_sampahs');
        Schema::dropIfExists('jenis_sampahs');
    }
};
