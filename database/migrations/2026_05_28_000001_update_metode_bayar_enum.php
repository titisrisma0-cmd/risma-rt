<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        // Add ewallet and qris to enum
        DB::statement("ALTER TABLE pembayarans MODIFY metode_bayar ENUM('tunai','transfer','ewallet','qris') NOT NULL");
    }

    public function down()
    {
        // Revert to original enum (may fail if rows use ewallet/qris)
        DB::statement("ALTER TABLE pembayarans MODIFY metode_bayar ENUM('tunai','transfer') NOT NULL");
    }
};
