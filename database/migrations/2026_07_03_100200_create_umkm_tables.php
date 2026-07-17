<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_usaha');
            $table->string('kategori');
            $table->text('deskripsi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('foto_toko')->nullable();
            $table->enum('status', ['draft', 'pending', 'aktif', 'ditolak', 'nonaktif'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('catatan_moderasi')->nullable();
            $table->timestamps();
        });

        Schema::create('produk_umkms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkms')->cascadeOnDelete();
            $table->string('nama_produk');
            $table->string('kategori')->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->integer('stok')->nullable();
            $table->string('satuan')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['draft', 'pending', 'aktif', 'ditolak', 'nonaktif'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('catatan_moderasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_umkms');
        Schema::dropIfExists('umkms');
    }
};
