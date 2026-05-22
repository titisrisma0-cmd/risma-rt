<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->decimal('nominal', 15, 2);
            $table->string('sumber')->nullable();
            $table->text('keterangan');
            $table->foreignId('pembayaran_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kas');
    }
};