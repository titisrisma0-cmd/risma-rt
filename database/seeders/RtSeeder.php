<?php

namespace Database\Seeders;

use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Database\Seeder;

class RtSeeder extends Seeder
{
    public function run(): void
    {
        $rw = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        foreach (range(1, 12) as $number) {
            $kode = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            Rt::updateOrCreate(
                ['rw_id' => $rw->id, 'kode_rt' => $kode],
                ['nama_rt' => 'RT '.$kode]
            );
        }
    }
}
