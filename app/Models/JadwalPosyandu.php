<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPosyandu extends Model
{
    protected $fillable = ['rt_id', 'judul', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'keterangan', 'latitude', 'longitude', 'created_by'];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanBalita::class);
    }
}
