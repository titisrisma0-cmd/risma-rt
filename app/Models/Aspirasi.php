<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aspirasi extends Model
{
    protected $fillable = [
        'user_id', 'rt_id', 'rw_id', 'kartu_keluarga_id', 'kategori', 'judul', 'deskripsi', 'lokasi', 'latitude', 'longitude', 'anonim',
        'status', 'assigned_to', 'resolved_at', 'solusi', 'escalated_at', 'escalation_target',
    ];

    protected $casts = [
        'anonim' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lampirans()
    {
        return $this->hasMany(AspirasiLampiran::class);
    }

    public function komentars()
    {
        return $this->hasMany(AspirasiKomentar::class);
    }

    public function tanggapans() { return $this->hasMany(AspirasiTanggapan::class); }
    public function rt() { return $this->belongsTo(Rt::class); }
    public function rw() { return $this->belongsTo(Rw::class); }
    public function kartuKeluarga() { return $this->belongsTo(KartuKeluarga::class); }
}
