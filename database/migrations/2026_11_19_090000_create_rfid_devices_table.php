<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rfid_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->unique();
            $table->string('nama_lokasi');
            $table->string('fungsi', 50)->default('belum_dikonfigurasi'); // gerbang, bank_sampah, presensi, perekaman, belum_dikonfigurasi
            $table->boolean('is_active')->default(true);
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_devices');
    }
};
