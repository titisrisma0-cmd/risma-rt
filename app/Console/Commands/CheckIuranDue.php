<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Iuran;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Notifikasi;
use Carbon\Carbon;

class CheckIuranDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iuran:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for unpaid iuran past due date and create notifications with penalty info';

    public function handle()
    {
        $now = Carbon::now();

$systemUserId = User::orderBy('id')->value('id') ?? 1;
        $iurans = Iuran::where('status', 'aktif')->get();
        foreach ($iurans as $iuran) {
            $dueDay = $iuran->due_day ?? 10;
            $dendaPercent = $iuran->denda_persen ?? 10.0;

            $year = $now->year;
            $month = $now->month;
            $dueDate = Carbon::createFromDate($year, $month, $dueDay)->endOfDay();

            if ($now->gt($dueDate)) {
                // cari semua warga
                $wargas = User::whereHas('role', function ($q) {
                    $q->where('nama_role', 'warga');
                })->get();

                foreach ($wargas as $warga) {
                    $bulanTahun = sprintf('%04d-%02d', $year, $month);
                    $exists = Pembayaran::where('user_id', $warga->id)
                        ->where('iuran_id', $iuran->id)
                        ->where('bulan_tahun', $bulanTahun)
                        ->where('status', 'lunas')
                        ->exists();

                    if (!$exists) {
                        // hitung denda
                        $dendaAmount = round(($iuran->nominal * ($dendaPercent / 100)), 2);

                        // hindari duplikasi notifikasi untuk periode yang sama
                        $already = Notifikasi::where('user_id', $warga->id)
                            ->where('iuran_id', $iuran->id)
                            ->where('judul', 'Iuran Belum Dibayar - Denda Dikenakan')
                            ->where('pesan', 'like', "%{$bulanTahun}%")
                            ->exists();

                        if (!$already) {
                            Notifikasi::create([
                                'user_id' => $warga->id,
                                'from_user_id' => $systemUserId,
                                'iuran_id' => $iuran->id,
                                'tipe' => 'iuran',
                                'judul' => 'Iuran Belum Dibayar - Denda Dikenakan',
                                'pesan' => "Anda belum membayar iuran {$iuran->nama_iuran} untuk periode {$bulanTahun}. Denda sebesar Rp " . number_format($dendaAmount, 0, ',', '.') . " telah dihitung.",
                                'dibaca' => false,
                            ]);
                        }
                    }
                }
            }
        }

        $this->info('CheckIuranDue completed.');
        return 0;
    }
}
