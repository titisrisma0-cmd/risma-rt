<?php

namespace Database\Seeders;

use App\Models\CctvCamera;
use App\Models\CctvRecording;
use App\Models\Rt;
use Illuminate\Database\Seeder;

class CctvDemoSeeder extends Seeder
{
    public function run(): void
    {
        $hls = 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8';
        $mp4 = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $locations = ['Gerbang Masuk', 'Pos Ronda', 'Jalan Utama'];

        Rt::orderBy('rw_id')->orderBy('kode_rt')->get()->each(function (Rt $rt, int $rtIndex) use ($hls, $mp4, $locations) {
            foreach ($locations as $index => $location) {
                $camera = CctvCamera::withoutGlobalScopes()->updateOrCreate(
                    ['rt_id' => $rt->id, 'nama_lokasi' => $location],
                    ['stream_url' => $hls, 'status' => $index === 2 && $rtIndex % 4 === 0 ? 'nonaktif' : 'aktif']
                );

                foreach (range(1, 3) as $day) {
                    $date = now()->subDays(($rtIndex + $index + $day) % 7)->toDateString();
                    $startHour = 18 + ($day * 2);
                    CctvRecording::withoutGlobalScopes()->updateOrCreate(
                        ['camera_id' => $camera->id, 'tanggal_rekaman' => $date, 'jam_mulai' => sprintf('%02d:00:00', $startHour)],
                        [
                            'file_path' => $mp4,
                            'jam_selesai' => sprintf('%02d:30:00', $startHour),
                            'durasi_detik' => 1800,
                            'ukuran_file_mb' => 42.50 + $index,
                        ]
                    );
                }
            }
        });
    }
}
