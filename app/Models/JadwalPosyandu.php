<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPosyandu extends Model
{
    protected $fillable = [
        'rt_id', 'judul', 'tanggal_mulai', 'tanggal_selesai', 'lokasi',
        'nama_bidan', 'kontak_bidan', 'kader_info', 'status',
        'keterangan', 'latitude', 'longitude', 'created_by'
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanBalita::class);
    }
}
