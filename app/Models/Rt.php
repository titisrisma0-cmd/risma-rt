<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rt extends Model
{
    protected $fillable = ['rw_id', 'kode_rt', 'nama_rt', 'ketua_user_id'];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    public function kartuKeluargas()
    {
        return $this->hasMany(KartuKeluarga::class);
    }

    public function wargaProfiles()
    {
        return $this->hasMany(UserProfile::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class);
    }

    public function cctvCameras()
    {
        return $this->hasMany(CctvCamera::class);
    }

    public function scopeActiveRt($query)
    {
        return $query->whereIn('kode_rt', ['01', '02', '03', '04', '05', '06', '07', '08']);
    }

    /**
     * Hitung statistik kependudukan akurat: KK, Jiwa, L, P, Belum Diketahui.
     * Mengikuti aturan:
     * - Total KK = jumlah KK aktif pada RT terkait
     * - Total Jiwa = seluruh anggota keluarga aktif (tidak termasuk pindah / meninggal)
     * - Laki-laki = jenis_kelamin L
     * - Perempuan = jenis_kelamin P
     * - Kosong / tidak valid = Belum Diketahui
     * - Total Jiwa = Laki-laki + Perempuan + Belum Diketahui
     */
    public function getStatistikKependudukan(): array
    {
        return self::getStatistikKependudukanWilayah($this->id);
    }

    public static function getStatistikKependudukanWilayah(?int $rtId = null): array
    {
        $queryKk = KartuKeluarga::query();
        $queryProfile = UserProfile::with('user:id,jenis_kelamin')
            ->where(function ($q) {
                $q->where('status', 'aktif')
                  ->orWhereNull('status');
            })
            ->whereNotIn('status', ['pindah', 'meninggal']);

        if ($rtId) {
            $queryKk->where('rt_id', $rtId);
            $queryProfile->where('rt_id', $rtId);
        } else {
            $queryKk->whereHas('rt', fn($q) => $q->activeRt());
            $queryProfile->whereHas('rt', fn($q) => $q->activeRt());
        }

        $kkCount = $queryKk->count();
        $activeProfiles = $queryProfile->get();

        $totalJiwa = $activeProfiles->count();
        $laki = 0;
        $perempuan = 0;
        $belumDiketahui = 0;

        foreach ($activeProfiles as $profile) {
            $userJk = $profile->user ? $profile->user->jenis_kelamin : null;
            $jk = $profile->jenis_kelamin ?: $userJk;
            $jk = strtoupper(trim((string) $jk));

            if ($jk === 'L' || $jk === 'LAKI-LAKI' || $jk === 'LAKI_LAKI') {
                $laki++;
            } elseif ($jk === 'P' || $jk === 'PEREMPUAN') {
                $perempuan++;
            } else {
                $belumDiketahui++;
            }
        }

        return [
            'total_kk' => $kkCount,
            'total_jiwa' => $totalJiwa,
            'laki_laki' => $laki,
            'perempuan' => $perempuan,
            'belum_diketahui' => $belumDiketahui,
            'total_laki' => $laki,
            'total_perempuan' => $perempuan,
            'total_belum_diketahui' => $belumDiketahui,
        ];
    }
}
