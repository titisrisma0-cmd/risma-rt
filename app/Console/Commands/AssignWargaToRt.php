<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Rt;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignWargaToRt extends Command
{
    protected $signature = 'warga:assign-rt
        {--commit : Simpan hasil assignment; tanpa opsi ini hanya menampilkan audit}
        {--include-missing-kk : Perlakukan warga tanpa No. KK sebagai unit sendiri berdasarkan NIK/user_id}';

    protected $description = 'Distribusikan keluarga warga ke RT tanpa menimpa rt_id yang sudah terisi';

    public function handle(): int
    {
        $roleId = Role::where('nama_role', 'warga')->value('id');
        $warga = UserProfile::where('role_id', $roleId)->orderBy('id')->get();
        $missingKk = $warga->filter(fn (UserProfile $profile) => blank($profile->no_kk));
        $uniqueKk = $warga->reject(fn (UserProfile $profile) => blank($profile->no_kk))->pluck('no_kk')->unique()->count();

        $this->table(['Statistik', 'Jumlah'], [
            ['Total warga', $warga->count()],
            ['KK unik terisi', $uniqueKk],
            ['Warga tanpa No. KK', $missingKk->count()],
            ['Sudah memiliki RT', $warga->whereNotNull('rt_id')->count()],
            ['Belum memiliki RT', $warga->whereNull('rt_id')->count()],
        ]);

        if (! $this->option('commit')) {
            $this->warn('Mode audit: tidak ada data yang diubah. Tambahkan --commit setelah hasil audit disetujui.');
            return self::SUCCESS;
        }

        if ($missingKk->isNotEmpty() && ! $this->option('include-missing-kk')) {
            $this->error('Assignment dibatalkan: ada warga tanpa No. KK. Gunakan --include-missing-kk hanya setelah disetujui.');
            return self::FAILURE;
        }

        $rts = Rt::orderBy('rw_id')->orderBy('kode_rt')->get();
        if ($rts->count() !== 12) {
            $this->error("Diperlukan tepat 12 RT; saat ini tersedia {$rts->count()} RT.");
            return self::FAILURE;
        }

        $groups = $warga->groupBy(fn (UserProfile $profile) => blank($profile->no_kk)
            ? 'PERSON:'.($profile->nik ?: $profile->user_id)
            : 'KK:'.trim($profile->no_kk));

        $loads = $rts->mapWithKeys(fn (Rt $rt) => [$rt->id => 0])->all();
        $assigned = 0;
        $conflicts = [];

        DB::transaction(function () use ($groups, $rts, &$loads, &$assigned, &$conflicts) {
            foreach ($groups->shuffle() as $key => $members) {
                $existingRtIds = $members->pluck('rt_id')->filter()->unique()->values();
                if ($existingRtIds->count() > 1) {
                    $conflicts[] = $key;
                    continue;
                }

                $rtId = $existingRtIds->first();
                if (! $rtId) {
                    $minimum = min($loads);
                    $candidateIds = collect($loads)->filter(fn ($load) => $load === $minimum)->keys();
                    $rtId = $candidateIds->random();
                }

                $unassigned = $members->whereNull('rt_id');
                if ($unassigned->isNotEmpty()) {
                    UserProfile::whereIn('id', $unassigned->pluck('id'))->update(['rt_id' => $rtId]);
                    DB::table('users')->whereIn('id', $unassigned->pluck('user_id'))->update(['rt_id' => $rtId]);
                    $assigned += $unassigned->count();
                }
                $loads[$rtId]++;
            }
        });

        $this->info("Berhasil menetapkan {$assigned} warga tanpa menimpa assignment manual.");
        if ($conflicts) {
            $this->warn('KK dengan assignment manual yang konflik dan dilewati: '.implode(', ', $conflicts));
        }
        $this->showDistribution($rts);

        return self::SUCCESS;
    }

    private function showDistribution(Collection $rts): void
    {
        $rows = $rts->map(function (Rt $rt) {
            $profiles = UserProfile::where('rt_id', $rt->id)->get(['no_kk', 'nik', 'user_id']);
            $families = $profiles->map(fn ($profile) => blank($profile->no_kk)
                ? 'PERSON:'.($profile->nik ?: $profile->user_id)
                : 'KK:'.trim($profile->no_kk))->unique()->count();

            return ['RT '.$rt->kode_rt, $families, $profiles->count()];
        });
        $this->table(['RT', 'KK/unit', 'Warga'], $rows);
    }
}
