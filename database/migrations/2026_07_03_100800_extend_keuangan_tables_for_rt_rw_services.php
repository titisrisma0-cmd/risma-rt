<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iurans', function (Blueprint $table) {
            if (! Schema::hasColumn('iurans', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->after('id')->constrained('rts')->nullOnDelete();
            }
            if (! Schema::hasColumn('iurans', 'rw_id')) {
                $table->foreignId('rw_id')->nullable()->after('rt_id')->constrained('rws')->nullOnDelete();
            }
        });

        Schema::table('kas', function (Blueprint $table) {
            if (! Schema::hasColumn('kas', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->after('id')->constrained('rts')->nullOnDelete();
            }
            if (! Schema::hasColumn('kas', 'rw_id')) {
                $table->foreignId('rw_id')->nullable()->after('rt_id')->constrained('rws')->nullOnDelete();
            }
            if (! Schema::hasColumn('kas', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('pembayaran_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            if (! Schema::hasColumn('pengeluarans', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->after('id')->constrained('rts')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'rw_id')) {
                $table->foreignId('rw_id')->nullable()->after('rt_id')->constrained('rws')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'nota_path')) {
                $table->string('nota_path')->nullable()->after('bukti');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            foreach (['rt_id', 'rw_id'] as $column) {
                if (Schema::hasColumn('pengeluarans', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            if (Schema::hasColumn('pengeluarans', 'nota_path')) {
                $table->dropColumn('nota_path');
            }
        });

        Schema::table('kas', function (Blueprint $table) {
            foreach (['rt_id', 'rw_id', 'created_by'] as $column) {
                if (Schema::hasColumn('kas', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });

        Schema::table('iurans', function (Blueprint $table) {
            foreach (['rt_id', 'rw_id'] as $column) {
                if (Schema::hasColumn('iurans', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
