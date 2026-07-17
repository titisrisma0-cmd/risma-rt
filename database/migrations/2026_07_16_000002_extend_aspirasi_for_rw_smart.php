<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspirasis', function (Blueprint $table) {
            $table->foreignId('rw_id')->nullable()->after('rt_id')->constrained('rws')->nullOnDelete();
            $table->foreignId('kartu_keluarga_id')->nullable()->after('user_id')->constrained('kartu_keluargas')->nullOnDelete();
            $table->string('lokasi')->nullable()->after('deskripsi');
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->timestamp('escalated_at')->nullable()->after('resolved_at');
            $table->string('escalation_target')->nullable()->after('escalated_at');
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aspirasis MODIFY status ENUM('terkirim','diproses','ditanggapi','dieskalasi','selesai','ditolak') NOT NULL DEFAULT 'terkirim'");
            DB::statement("UPDATE aspirasis SET status = 'terkirim' WHERE status = 'pending'");
        }
        Schema::create('aspirasi_tanggapans', function (Blueprint $table) {
            $table->id(); $table->foreignId('aspirasi_id')->constrained('aspirasis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('isi'); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('aspirasi_tanggapans');
        Schema::table('aspirasis', function (Blueprint $table) { $table->dropConstrainedForeignId('rw_id'); $table->dropConstrainedForeignId('kartu_keluarga_id'); $table->dropColumn(['lokasi','latitude','longitude','escalated_at','escalation_target']); });
    }
};
