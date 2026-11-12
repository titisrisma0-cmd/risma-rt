<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiWarga extends Model
{
    use HasFactory;

    protected $table = 'mutasi_wargas';

    protected $fillable = [
        'rt_id',
        'rt_id_tujuan',
        'user_profile_id',
        'nama_warga',
        'nama_kepala_keluarga',
        'nik',
        'no_kk',
        'alamat_asal',
        'alamat_tujuan',
        'anggota_keluarga',
        'dokumen_pendukung',
        'jenis_mutasi',
        'tanggal_mutasi',
        'keterangan',
        'status_verifikasi',
        'created_by',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
        'verified_at' => 'datetime',
        'anggota_keluarga' => 'array',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function rtTujuan()
    {
        return $this->belongsTo(Rt::class, 'rt_id_tujuan');
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
