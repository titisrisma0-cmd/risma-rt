<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('iurans', function (Blueprint $table) {
            if (!Schema::hasColumn('iurans', 'due_day')) {
                $table->unsignedTinyInteger('due_day')->default(10)->after('periode');
            }
            if (!Schema::hasColumn('iurans', 'denda_persen')) {
                $table->decimal('denda_persen', 5, 2)->default(10.00)->after('due_day');
            }
        });
    }

    public function down()
    {
        Schema::table('iurans', function (Blueprint $table) {
            if (Schema::hasColumn('iurans', 'denda_persen')) {
                $table->dropColumn('denda_persen');
            }
            if (Schema::hasColumn('iurans', 'due_day')) {
                $table->dropColumn('due_day');
            }
        });
    }
};
