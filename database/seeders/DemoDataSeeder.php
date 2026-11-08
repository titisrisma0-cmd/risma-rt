<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use App\Models\Iuran;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Buat Role jika belum ada
        $adminRole = Role::firstOrCreate(['nama_role' => 'admin']);
        $bendaharaRole = Role::firstOrCreate(['nama_role' => 'bendahara']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);
        
        // Buat Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@rw-smart.test'],
            [
                'name' => 'Admin RW Smart',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $admin->id],
            ['nama_lengkap' => 'Admin RW Smart', 'status' => 'aktif', 'role_id' => $adminRole->id]
        );
        
        // Buat Bendahara
        $bendahara = User::updateOrCreate(
            ['email' => 'bendahara@rw-smart.test'],
            [
                'name' => 'Bendahara RW',
                'password' => Hash::make('password123'),
                'role_id' => $bendaharaRole->id
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $bendahara->id],
            ['nama_lengkap' => 'Bendahara RW', 'status' => 'aktif', 'role_id' => $bendaharaRole->id]
        );
        
        // Buat Warga Utama (Demo)
        $wargaDemo = User::updateOrCreate(
            ['email' => 'warga@rw-smart.test'],
            [
                'name' => 'Warga RW Smart',
                'password' => Hash::make('password123'),
                'role_id' => $wargaRole->id,
                'jenis_kelamin' => 'L',
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $wargaDemo->id],
            [
                'nama_lengkap' => 'Warga RW Smart',
                'nik' => '1234567890123400',
                'no_kk' => '9876543210987600',
                'no_hp' => '081234567800',
                'alamat' => 'Jl. Contoh No. 0, RT 01/RW 02',
                'status' => 'aktif',
                'role_id' => $wargaRole->id,
                'jenis_kelamin' => 'L',
            ]
        );

        // Buat 5 Warga
        for ($i = 1; $i <= 5; $i++) {
            $jk = $i % 2 === 0 ? 'P' : 'L';
            $warga = User::updateOrCreate(
                ['email' => "warga$i@rw-smart.test"],
                [
                    'name' => "Warga $i",
                    'password' => Hash::make('password123'),
                    'role_id' => $wargaRole->id,
                    'jenis_kelamin' => $jk,
                ]
            );
            UserProfile::updateOrCreate(
                ['user_id' => $warga->id],
                [
                    'nama_lengkap' => "Warga $i",
                    'nik' => "123456789012345$i",
                    'no_kk' => "987654321098765$i",
                    'no_hp' => "08123456789$i",
                    'alamat' => "Jl. Contoh No. $i, RT 01/RW 02",
                    'status' => 'aktif',
                    'role_id' => $wargaRole->id,
                    'jenis_kelamin' => $jk,
                ]
            );
        }
        
        // Buat Iuran
        $iuranList = [
            ['kode_iuran' => 'IUR001', 'nama_iuran' => 'Iuran Wajib Bulanan', 'jenis_iuran' => 'wajib', 'nominal' => 50000, 'periode' => 'bulanan', 'status' => 'aktif'],
            ['kode_iuran' => 'IUR002', 'nama_iuran' => 'Iuran Keamanan', 'jenis_iuran' => 'wajib', 'nominal' => 20000, 'periode' => 'bulanan', 'status' => 'aktif'],
            ['kode_iuran' => 'IUR003', 'nama_iuran' => 'Iuran Kebersihan', 'jenis_iuran' => 'wajib', 'nominal' => 15000, 'periode' => 'bulanan', 'status' => 'aktif'],
            ['kode_iuran' => 'IUR004', 'nama_iuran' => 'Dana Sosial', 'jenis_iuran' => 'sukarela', 'nominal' => 10000, 'periode' => 'bulanan', 'status' => 'aktif'],
        ];
        
        foreach ($iuranList as $iuran) {
            Iuran::updateOrCreate(['kode_iuran' => $iuran['kode_iuran']], $iuran);
        }
        
        // Buat Pembayaran contoh
        $wargas = User::where('role_id', $wargaRole->id)->get();
        $iurans = Iuran::all();
        
        for ($i = 1; $i <= 10; $i++) {
            $warga = $wargas->random();
            $iuran = $iurans->random();
            
            Pembayaran::create([
                'no_transaksi' => 'TRX' . date('Ymd') . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_id' => $warga->id,
                'iuran_id' => $iuran->id,
                'tanggal_bayar' => now()->subDays(rand(1, 30)),
                'jumlah_bayar' => $iuran->nominal,
                'metode_bayar' => rand(0, 1) ? 'tunai' : 'transfer',
                'status' => 'lunas',
                'bulan_tahun' => now()->format('Y-m'),
                'verified_by' => $bendahara->id,
                'verified_at' => now()
            ]);
        }
        
        // Buat Kas Masuk dari pembayaran
        $pembayaran = Pembayaran::where('status', 'lunas')->get();
        foreach ($pembayaran as $bayar) {
            Kas::updateOrCreate(
                ['pembayaran_id' => $bayar->id],
                [
                    'tanggal' => $bayar->tanggal_bayar,
                    'jenis' => 'masuk',
                    'nominal' => $bayar->jumlah_bayar,
                    'sumber' => 'Iuran',
                    'keterangan' => 'Pembayaran iuran dari ' . $bayar->user->name
                ]
            );
        }
        
        // Buat Pengeluaran contoh
        $pengeluaranList = [
            ['tanggal' => now()->subDays(5), 'kategori' => 'Kebersihan', 'nominal' => 100000, 'keterangan' => 'Beli sapu dan alat kebersihan'],
            ['tanggal' => now()->subDays(10), 'kategori' => 'Keamanan', 'nominal' => 150000, 'keterangan' => 'Beli lampu untuk poskamling'],
            ['tanggal' => now()->subDays(15), 'kategori' => 'Sosial', 'nominal' => 200000, 'keterangan' => 'Sumbangan untuk warga sakit'],
            ['tanggal' => now()->subDays(20), 'kategori' => 'Perlengkapan', 'nominal' => 75000, 'keterangan' => 'Beli alat tulis kantor'],
        ];
        
        foreach ($pengeluaranList as $peng) {
            Pengeluaran::create([
                'tanggal' => $peng['tanggal'],
                'kategori' => $peng['kategori'],
                'nominal' => $peng['nominal'],
                'keterangan' => $peng['keterangan'],
                'created_by' => $bendahara->id
            ]);
            
            Kas::create([
                'tanggal' => $peng['tanggal'],
                'jenis' => 'keluar',
                'nominal' => $peng['nominal'],
                'sumber' => $peng['kategori'],
                'keterangan' => $peng['keterangan']
            ]);
        }
        
        $this->command->info('Demo data berhasil dibuat!');
    }
}
