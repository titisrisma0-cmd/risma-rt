<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rt_id', 'rw_id', 'cakupan', 'judul', 'kategori', 'tanggal_mulai',
        'tanggal_selesai', 'lokasi', 'latitude', 'longitude', 'kuota_peserta', 'deskripsi', 'status', 'created_by', 'creator_role', 'deleted_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function getNamaKegiatanAttribute(): string
    {
        return $this->attributes['judul'] ?? '';
    }

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

    public function creator()
    {
        return $this->pembuat();
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
