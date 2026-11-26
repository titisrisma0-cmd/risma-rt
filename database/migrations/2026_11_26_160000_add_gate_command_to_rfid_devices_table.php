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
        Schema::table('rfid_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('rfid_devices', 'gate_command')) {
                $table->boolean('gate_command')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('rfid_devices', 'gate_open_until')) {
                $table->timestamp('gate_open_until')->nullable()->after('gate_command');
            }
            if (! Schema::hasColumn('rfid_devices', 'gate_auto_close_seconds')) {
                $table->unsignedSmallInteger('gate_auto_close_seconds')->default(5)->after('gate_open_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfid_devices', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('rfid_devices', 'gate_command') ? 'gate_command' : null,
                Schema::hasColumn('rfid_devices', 'gate_open_until') ? 'gate_open_until' : null,
                Schema::hasColumn('rfid_devices', 'gate_auto_close_seconds') ? 'gate_auto_close_seconds' : null,
            ]);
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
