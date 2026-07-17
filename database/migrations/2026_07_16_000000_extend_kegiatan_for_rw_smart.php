<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->string('cakupan', 2)->default('rw')->after('rw_id');
            $table->unsignedInteger('kuota_peserta')->nullable()->after('lokasi');
            $table->string('creator_role', 30)->nullable()->after('created_by');
        });

        // Existing installations used an enum that did not include dibuka/berlangsung.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kegiatans MODIFY status ENUM('draft','dibuka','berlangsung','selesai','dibatalkan') NOT NULL DEFAULT 'draft'");
            DB::statement("UPDATE kegiatans SET status = 'dibuka' WHERE status = 'terbit'");
        }

        Schema::create('kegiatan_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['terdaftar', 'hadir', 'tidak_hadir'])->default('terdaftar');
            $table->timestamps();
            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_pesertas');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kegiatans MODIFY status ENUM('draft','terbit','selesai','dibatalkan') NOT NULL DEFAULT 'terbit'");
        }

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropColumn(['cakupan', 'kuota_peserta', 'creator_role']);
        });
    }
};
