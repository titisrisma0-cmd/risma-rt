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
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'rfid_card_type')) {
                $table->string('rfid_card_type', 100)->nullable()->after('rfid_uid');
            }
            if (!Schema::hasColumn('user_profiles', 'rfid_registered_at')) {
                $table->timestamp('rfid_registered_at')->nullable()->after('rfid_card_type');
            }
            if (!Schema::hasColumn('user_profiles', 'rfid_registered_by')) {
                $table->string('rfid_registered_by', 100)->nullable()->after('rfid_registered_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['rfid_card_type', 'rfid_registered_at', 'rfid_registered_by']);
        });
    }
};
