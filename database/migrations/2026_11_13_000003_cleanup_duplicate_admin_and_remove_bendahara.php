<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 1. Jadikan Titis Rismawati Super Admin dengan password 'password123'.
     * 2. Hapus duplikat admin@risma-rt.test dan pertahankan hanya admin@rw-smart.test sebagai role RW.
     * 3. Hapus seluruh akun bendahara (agar RW fokus ke perannya sendiri tanpa butuh bendahara).
     */
    public function up(): void
    {
        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $rwRole = Role::where('nama_role', 'rw')->first() ?? Role::where('nama_role', 'admin')->first();

        // 1. Pastikan Akun Super Admin TITIS RISMAWATI aktif dengan password 'password123'
        DB::table('users')
            ->where('email', 'titisrisma0@gmail.com')
            ->update([
                'name' => 'Titis Rismawati',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole->id,
                'is_super_admin' => 1,
            ]);

        // 2. Cari target ID yang akan dihapus: admin lama & bendahara
        $targetEmails = [
            'admin@risma-rt.test',
            'bendahara@risma-rt.test',
            'bendahara@rw-smart.test',
        ];

        $targetUserIds = DB::table('users')
            ->whereIn('email', $targetEmails)
            ->pluck('id')
            ->toArray();

        // Cari ID utama Admin RW yang dipertahankan
        $mainRwUser = DB::table('users')->where('email', 'admin@rw-smart.test')->first();
        $mainRwId = $mainRwUser ? $mainRwUser->id : null;

        if ($mainRwId) {
            // Update kredensial Admin RW
            DB::table('users')
                ->where('id', $mainRwId)
                ->update([
                    'password' => Hash::make('password123'),
                    'role_id' => $rwRole ? $rwRole->id : 4,
                    'is_super_admin' => 0,
                ]);

            if (!empty($targetUserIds)) {
                // Reassign seluruh referensi audit/transaksi ke Admin RW utama agar tidak ada relasi putus
                $reassignTables = [
                    'jadwal_rondas' => 'created_by',
                    'kegiatans' => 'created_by',
                    'laporans' => 'created_by',
                    'pembayarans' => 'verified_by',
                    'pengajuan_surats' => 'approved_by',
                    'pengeluarans' => 'created_by',
                    'setoran_sampahs' => 'created_by',
                    'umkms' => 'user_id',
                    'aspirasis' => 'user_id',
                ];

                foreach ($reassignTables as $table => $column) {
                    if (\Illuminate\Support\Facades\Schema::hasTable($table) && \Illuminate\Support\Facades\Schema::hasColumn($table, $column)) {
                        DB::table($table)->whereIn($column, $targetUserIds)->update([$column => $mainRwId]);
                    }
                }
            }
        }

        // 3. Bersihkan data anak dari user yang dihapus
        if (!empty($targetUserIds)) {
            if (\Illuminate\Support\Facades\Schema::hasTable('kegiatan_pesertas')) {
                DB::table('kegiatan_pesertas')->whereIn('user_id', $targetUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('notifikasis')) {
                DB::table('notifikasis')->whereIn('user_id', $targetUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->whereIn('user_id', $targetUserIds)->update(['user_id' => $mainRwId]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('user_profiles')) {
                DB::table('user_profiles')->whereIn('user_id', $targetUserIds)->delete();
            }

            // Hapus user dari tabel users
            DB::table('users')->whereIn('id', $targetUserIds)->delete();
        }

        // Hapus juga jika ada user lain yang masih memiliki role bendahara
        $bendaharaRole = Role::where('nama_role', 'bendahara')->first();
        if ($bendaharaRole) {
            $otherBendaharaIds = DB::table('users')->where('role_id', $bendaharaRole->id)->pluck('id')->toArray();
            if (!empty($otherBendaharaIds)) {
                if ($mainRwId) {
                    DB::table('pembayarans')->whereIn('verified_by', $otherBendaharaIds)->update(['verified_by' => $mainRwId]);
                }
                DB::table('user_profiles')->whereIn('user_id', $otherBendaharaIds)->delete();
                DB::table('users')->whereIn('id', $otherBendaharaIds)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
