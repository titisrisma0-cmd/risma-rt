<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update metode_pembayarans
        if (Schema::hasTable('metode_pembayarans')) {
            DB::table('metode_pembayarans')
                ->where('nama', 'QRIS RW SMART')
                ->update(['nama' => 'QRIS SMART RW']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'RW 05 SMART')
                ->update(['nama_penerima' => 'SMART RW 01']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'Bendahara RW SMART')
                ->update(['nama_penerima' => 'Bendahara SMART RW']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'KAS PENGURUS RW SMART')
                ->update(['nama_penerima' => 'KAS PENGURUS SMART RW']);
        }

        // 2. Update users name
        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('name', 'Admin RW Smart')
                ->update(['name' => 'Admin Smart RW']);

            DB::table('users')
                ->where('name', 'Warga RW Smart')
                ->update(['name' => 'Warga Smart RW']);
        }

        // 3. Update user_profiles
        if (Schema::hasTable('user_profiles')) {
            DB::table('user_profiles')
                ->where('nama_lengkap', 'Admin RW Smart')
                ->update(['nama_lengkap' => 'Admin Smart RW']);

            DB::table('user_profiles')
                ->where('nama_lengkap', 'Warga RW Smart')
                ->update(['nama_lengkap' => 'Warga Smart RW']);
        }

        // 4. Update balitas
        if (Schema::hasTable('balitas')) {
            DB::table('balitas')
                ->where('nama', 'Warga RW Smart')
                ->update(['nama' => 'Warga Smart RW']);
        }

        // 5. Update kas
        if (Schema::hasTable('kas')) {
            DB::table('kas')
                ->where('keterangan', 'LIKE', '%Warga RW Smart%')
                ->update([
                    'keterangan' => DB::raw("REPLACE(keterangan, 'Warga RW Smart', 'Warga Smart RW')")
                ]);
        }

        // 6. Update notifikasis
        if (Schema::hasTable('notifikasis')) {
            DB::table('notifikasis')
                ->where('pesan', 'LIKE', '%Warga RW Smart%')
                ->update([
                    'pesan' => DB::raw("REPLACE(pesan, 'Warga RW Smart', 'Warga Smart RW')")
                ]);
        }

        // 7. Update audit_logs
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('deskripsi', 'LIKE', '%Admin RW Smart%')
                ->update([
                    'deskripsi' => DB::raw("REPLACE(deskripsi, 'Admin RW Smart', 'Admin Smart RW')")
                ]);

            DB::table('audit_logs')
                ->where('deskripsi', 'LIKE', '%Warga RW Smart%')
                ->update([
                    'deskripsi' => DB::raw("REPLACE(deskripsi, 'Warga RW Smart', 'Warga Smart RW')")
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert metode_pembayarans
        if (Schema::hasTable('metode_pembayarans')) {
            DB::table('metode_pembayarans')
                ->where('nama', 'QRIS SMART RW')
                ->update(['nama' => 'QRIS RW SMART']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'SMART RW 01')
                ->update(['nama_penerima' => 'RW 05 SMART']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'Bendahara SMART RW')
                ->update(['nama_penerima' => 'Bendahara RW SMART']);

            DB::table('metode_pembayarans')
                ->where('nama_penerima', 'KAS PENGURUS SMART RW')
                ->update(['nama_penerima' => 'KAS PENGURUS RW SMART']);
        }

        // 2. Revert users
        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('name', 'Admin Smart RW')
                ->update(['name' => 'Admin RW Smart']);

            DB::table('users')
                ->where('name', 'Warga Smart RW')
                ->update(['name' => 'Warga RW Smart']);
        }

        // 3. Revert user_profiles
        if (Schema::hasTable('user_profiles')) {
            DB::table('user_profiles')
                ->where('nama_lengkap', 'Admin Smart RW')
                ->update(['nama_lengkap' => 'Admin RW Smart']);

            DB::table('user_profiles')
                ->where('nama_lengkap', 'Warga Smart RW')
                ->update(['nama_lengkap' => 'Warga RW Smart']);
        }

        // 4. Revert balitas
        if (Schema::hasTable('balitas')) {
            DB::table('balitas')
                ->where('nama', 'Warga Smart RW')
                ->update(['nama' => 'Warga RW Smart']);
        }

        // 5. Revert kas
        if (Schema::hasTable('kas')) {
            DB::table('kas')
                ->where('keterangan', 'LIKE', '%Warga Smart RW%')
                ->update([
                    'keterangan' => DB::raw("REPLACE(keterangan, 'Warga Smart RW', 'Warga RW Smart')")
                ]);
        }

        // 6. Revert notifikasis
        if (Schema::hasTable('notifikasis')) {
            DB::table('notifikasis')
                ->where('pesan', 'LIKE', '%Warga Smart RW%')
                ->update([
                    'pesan' => DB::raw("REPLACE(pesan, 'Warga Smart RW', 'Warga RW Smart')")
                ]);
        }

        // 7. Revert audit_logs
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('deskripsi', 'LIKE', '%Admin Smart RW%')
                ->update([
                    'deskripsi' => DB::raw("REPLACE(deskripsi, 'Admin Smart RW', 'Admin RW Smart')")
                ]);

            DB::table('audit_logs')
                ->where('deskripsi', 'LIKE', '%Warga Smart RW%')
                ->update([
                    'deskripsi' => DB::raw("REPLACE(deskripsi, 'Warga Smart RW', 'Warga RW Smart')")
                ]);
        }
    }
};
