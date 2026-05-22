<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('iurans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_iuran')->unique();
            $table->string('nama_iuran');
            $table->enum('jenis_iuran', ['wajib', 'sukarela', 'khusus']);
            $table->decimal('nominal', 15, 2);
            $table->enum('periode', ['bulanan', 'triwulan', 'tahunan']);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('iurans');
    }
};