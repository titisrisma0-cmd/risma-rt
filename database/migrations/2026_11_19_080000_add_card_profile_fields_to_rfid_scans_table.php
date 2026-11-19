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
        Schema::table('rfid_scans', function (Blueprint $table) {
            if (!Schema::hasColumn('rfid_scans', 'sak')) {
                $table->string('sak', 10)->nullable()->after('card_type');
            }
            if (!Schema::hasColumn('rfid_scans', 'atqa')) {
                $table->string('atqa', 10)->nullable()->after('sak');
            }
            if (!Schema::hasColumn('rfid_scans', 'uid_size')) {
                $table->unsignedTinyInteger('uid_size')->nullable()->after('atqa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfid_scans', function (Blueprint $table) {
            $table->dropColumn(['sak', 'atqa', 'uid_size']);
        });
    }
};
