<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Move any payments from duplicate iuran ID 15 to master iuran ID 4 ("Dana Sosial")
        DB::table('pembayarans')
            ->where('iuran_id', 15)
            ->update(['iuran_id' => 4]);

        // 2. Safely deactivate verified duplicate records (ID 9, 10, 12, 15)
        // This preserves the database primary keys and audit trails without deleting history
        DB::table('iurans')
            ->whereIn('id', [9, 10, 12, 15])
            ->update([
                'status' => 'nonaktif',
                'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' [Duplikat dinonaktifkan via audit]')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('iurans')
            ->whereIn('id', [9, 10, 12, 15])
            ->update(['status' => 'aktif']);
    }
};
