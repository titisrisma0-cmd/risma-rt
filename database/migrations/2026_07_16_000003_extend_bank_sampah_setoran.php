<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('setoran_sampahs',function(Blueprint $t){$t->date('tanggal_setor')->nullable()->after('total_saldo');$t->string('foto_bukti')->nullable()->after('tanggal_setor');}); Schema::create('jadwal_bank_sampahs',function(Blueprint $t){$t->id();$t->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();$t->string('judul');$t->dateTime('waktu');$t->string('lokasi');$t->text('keterangan')->nullable();$t->boolean('aktif')->default(true);$t->timestamps();}); }
 public function down(): void {Schema::dropIfExists('jadwal_bank_sampahs');Schema::table('setoran_sampahs',function(Blueprint $t){$t->dropColumn(['tanggal_setor','foto_bukti']);});}
};
