<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengumumans')) {
            Schema::create('pengumumans', function (Blueprint $table) {
                $table->id();
                $table->string('judul');
                $table->text('isi');
                $table->string('kategori')->default('Umum');
                $table->string('target_audience')->default('semua_rt'); // semua_rt, rt_tertentu, seluruh_warga, warga_rt
                $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
                $table->dateTime('tanggal_publikasi')->nullable();
                $table->string('status')->default('published'); // draft, published, arsip
                $table->string('lampiran')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
    }
};
