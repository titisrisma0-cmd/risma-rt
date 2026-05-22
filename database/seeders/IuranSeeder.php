<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Iuran;

class IuranSeeder extends Seeder
{
    public function run()
    {
        $iurans = [
            [
                'kode_iuran' => 'IUR001',
                'nama_iuran' => 'Iuran Wajib Bulanan',
                'jenis_iuran' => 'wajib',
                'nominal' => 50000,
                'periode' => 'bulanan',
                'keterangan' => 'Iuran rutin untuk kebersihan dan keamanan',
                'status' => 'aktif'
            ],
            [
                'kode_iuran' => 'IUR002',
                'nama_iuran' => 'Iuran Keamanan',
                'jenis_iuran' => 'wajib',
                'nominal' => 20000,
                'periode' => 'bulanan',
                'keterangan' => 'Iuran untuk poskamling dan satpam',
                'status' => 'aktif'
            ],
            [
                'kode_iuran' => 'IUR003',
                'nama_iuran' => 'Dana Sosial',
                'jenis_iuran' => 'sukarela',
                'nominal' => 10000,
                'periode' => 'bulanan',
                'keterangan' => 'Untuk kegiatan sosial dan bantuan',
                'status' => 'aktif'
            ],
            [
                'kode_iuran' => 'IUR004',
                'nama_iuran' => 'Iuran Pembangunan',
                'jenis_iuran' => 'khusus',
                'nominal' => 100000,
                'periode' => 'tahunan',
                'keterangan' => 'Iuran untuk pembangunan infrastruktur',
                'status' => 'aktif'
            ]
        ];
        
        foreach ($iurans as $iuran) {
            Iuran::create($iuran);
        }
    }
}