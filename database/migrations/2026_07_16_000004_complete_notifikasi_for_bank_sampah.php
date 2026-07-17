<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('notifikasis', function(Blueprint $t){ if(!Schema::hasColumn('notifikasis','user_id'))$t->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete(); if(!Schema::hasColumn('notifikasis','tipe'))$t->string('tipe')->nullable(); if(!Schema::hasColumn('notifikasis','judul'))$t->string('judul')->nullable(); if(!Schema::hasColumn('notifikasis','pesan'))$t->text('pesan')->nullable(); if(!Schema::hasColumn('notifikasis','dibaca'))$t->boolean('dibaca')->default(false); }); } public function down(): void {} };
