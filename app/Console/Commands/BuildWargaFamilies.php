<?php

namespace App\Console\Commands;

use App\Models\KartuKeluarga;
use App\Models\Role;
use App\Models\Rt;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BuildWargaFamilies extends Command
{
    protected $signature = 'warga:build-families {--commit : Simpan pembentukan ulang keluarga}';

    protected $description = 'Bentuk KK berisi tepat lima warga dan tempatkan satu KK dalam RT yang sama';

    public function handle(): int
    {
        $role = Role::where('nama_role', 'warga')->firstOrFail();
        $count = User::where('role_id', $role->id)->count();
        $fillCount = (5 - ($count % 5)) % 5;

        $this->table(['Data', 'Jumlah'], [
            ['Warga saat ini', $count],
            ['Anggota pelengkap', $fillCount],
            ['Total setelah proses', $count + $fillCount],
            ['Jumlah KK', intdiv($count + $fillCount, 5)],
            ['Anggota setiap KK', 5],
        ]);

        if (! $this->option('commit')) {
            $this->warn('Mode pratinjau; tambahkan --commit untuk menyimpan.');
            return self::SUCCESS;
        }

        $rts = Rt::with('rw')->orderBy('rw_id')->orderBy('kode_rt')->get();
        if ($rts->isEmpty()) {
            $this->error('Data RT belum tersedia.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($role, $fillCount, $rts) {
            for ($number = 1; $number <= $fillCount; $number++) {
                $suffix = now()->format('YmdHis').$number;
                $user = User::create([
                    'name' => 'Anggota Keluarga Tambahan '.$number,
                    'email' => 'anggota.kk.'.$suffix.'@rw-smart.test',
                    'password' => Hash::make('password123'),
                    'role_id' => $role->id,
                ]);
                UserProfile::updateOrCreate(['user_id' => $user->id], [
                    'role_id' => $role->id,
                    'nik' => '99'.str_pad((string) $user->id, 14, '0', STR_PAD_LEFT),
                    'nama_lengkap' => $user->name,
                    'status' => 'aktif',
                ]);
            }

            $members = User::where('role_id', $role->id)->with('profile')->orderBy('id')->get();
            foreach ($members as $member) {
                $profiles = UserProfile::where('user_id', $member->id)->orderBy('id')->get();
                $profile = $profiles->first();
                if ($profiles->count() > 1) {
                    UserProfile::whereIn('id', $profiles->slice(1)->pluck('id'))->delete();
                }

                UserProfile::updateOrCreate(['id' => $profile?->id], [
                    'user_id' => $member->id,
                    'role_id' => $role->id,
                    'nama_lengkap' => $profile?->nama_lengkap ?: $member->name,
                    'status' => $profile?->status ?: 'aktif',
                ]);
            }

            $members = User::where('role_id', $role->id)->with('profile')->orderBy('id')->get();
            foreach ($members->chunk(5)->values() as $familyIndex => $family) {
                $rt = $rts[$familyIndex % $rts->count()];
                $head = $family->first();
                $noKk = sprintf('3374%06d%06d', $rt->id, $familyIndex + 1);
                $kk = KartuKeluarga::updateOrCreate(['no_kk' => $noKk], [
                    'rt_id' => $rt->id,
                    'kepala_keluarga_user_id' => $head->id,
                    'alamat' => $head->profile?->alamat ?: 'Alamat belum diisi',
                    'status_rumah' => 'tetap',
                ]);

                UserProfile::whereIn('user_id', $family->pluck('id'))->update([
                    'role_id' => $role->id,
                    'rw_id' => $rt->rw_id,
                    'rt_id' => $rt->id,
                    'kartu_keluarga_id' => $kk->id,
                    'no_kk' => $noKk,
                ]);
                User::whereIn('id', $family->pluck('id'))->update(['rt_id' => $rt->id]);
            }
        });

        $invalidFamilies = UserProfile::query()
            ->whereHas('user', fn ($query) => $query->where('role_id', $role->id))
            ->select('no_kk', DB::raw('COUNT(*) as total'))
            ->groupBy('no_kk')->having('total', '!=', 5)->count();

        $total = User::where('role_id', $role->id)->count();
        $this->info("Berhasil membentuk ".intdiv($total, 5)." KK dari {$total} warga.");
        $this->line('KK yang tidak berisi tepat 5 anggota: '.$invalidFamilies);

        return $invalidFamilies === 0 ? self::SUCCESS : self::FAILURE;
    }
}
