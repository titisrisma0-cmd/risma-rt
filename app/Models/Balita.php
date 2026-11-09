<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balita extends Model
{
    protected $fillable = [
        'rt_id', 'kartu_keluarga_id', 'user_profile_id', 'orang_tua_user_id', 'nama', 'nik',
        'tanggal_lahir', 'jenis_kelamin', 'nama_orang_tua', 'no_hp_orang_tua', 'catatan_kesehatan',
    ];

    protected $casts = ['tanggal_lahir' => 'date'];

    public function kartuKeluarga()
    {
        return $this->belongsTo(KartuKeluarga::class);
    }

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function orangTua()
    {
        return $this->belongsTo(User::class, 'orang_tua_user_id');
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanBalita::class);
    }

    public function getNamaWaliAttribute(): string
    {
        if (!empty($this->nama_orang_tua)) {
            return $this->nama_orang_tua;
        }
        if ($this->orangTua) {
            return $this->orangTua->name;
        }
        if ($this->userProfile) {
            return $this->userProfile->nama_ayah ?: ($this->userProfile->nama_ibu ?: ($this->kartuKeluarga?->nama_kepala_keluarga ?? '-'));
        }
        return '-';
    }
}
