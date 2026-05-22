<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayarans', 'bukti_transfer')) {
                $table->string('bukti_transfer')->nullable()->after('metode_bayar');
            }
        });
    }

    public function down()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('bukti_transfer');
        });
    }
};