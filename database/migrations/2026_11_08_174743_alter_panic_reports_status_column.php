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
        Schema::table('panic_reports', function (Blueprint $table) {
            $table->string('status', 50)->default('aktif')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panic_reports', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'ditangani', 'selesai', 'false_alarm'])->default('aktif')->change();
        });
    }
};
