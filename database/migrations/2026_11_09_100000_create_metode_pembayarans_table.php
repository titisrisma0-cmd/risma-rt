<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('metode_pembayarans')) {
            Schema::create('metode_pembayarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama', 100);
                $table->string('kategori', 50)->default('ewallet'); // 'ewallet', 'qris', 'bank', 'tunai'
                $table->string('nama_penerima')->nullable();
                $table->string('nomor_rekening')->nullable();
                $table->string('qr_image')->nullable();
                $table->text('instruksi')->nullable();
                $table->boolean('is_active')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('metode_pembayarans');
    }
};
