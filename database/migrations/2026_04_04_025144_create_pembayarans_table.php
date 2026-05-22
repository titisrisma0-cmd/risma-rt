<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('iuran_id')->constrained();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_bayar', ['tunai', 'transfer']);
            $table->enum('status', ['pending', 'lunas', 'gagal'])->default('pending');
            $table->text('bukti_bayar')->nullable();
            $table->string('bulan_tahun')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayarans');
    }
};