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
        Schema::create('rfid_scans', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 64)->index();
            $table->string('formatted_uid', 64)->nullable();
            $table->string('card_type', 100)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->foreignId('device_token_id')->nullable()->constrained('device_tokens')->nullOnDelete();
            $table->foreignId('user_profile_id')->nullable()->constrained('user_profiles')->nullOnDelete();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('nama_warga')->nullable();
            $table->boolean('access_granted')->default(false);
            $table->string('buzzer_signal', 20)->default('DENIED');
            $table->string('access_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('scanned_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_scans');
    }
};
