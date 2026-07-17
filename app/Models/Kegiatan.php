<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $fillable = [
        'rt_id', 'rw_id', 'cakupan', 'judul', 'kategori', 'tanggal_mulai',
        'tanggal_selesai', 'lokasi', 'kuota_peserta', 'deskripsi', 'status', 'created_by', 'creator_role',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function rsvps()
    {
        return $this->hasMany(RsvpKegiatan::class);
    }

    public function peserta()
    {
        return $this->hasMany(KegiatanPeserta::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
