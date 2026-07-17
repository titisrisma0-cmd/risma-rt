<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balita extends Model
{
    protected $fillable = [
        'kartu_keluarga_id', 'orang_tua_user_id', 'nama', 'nik',
        'tanggal_lahir', 'jenis_kelamin', 'catatan_kesehatan',
    ];

    protected $casts = ['tanggal_lahir' => 'date'];

    public function kartuKeluarga()
    {
        return $this->belongsTo(KartuKeluarga::class);
    }

    public function orangTua()
    {
        return $this->belongsTo(User::class, 'orang_tua_user_id');
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanBalita::class);
    }
}
