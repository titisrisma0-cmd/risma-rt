<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalRonda extends Model
{
    protected $fillable = ['rt_id', 'tanggal', 'shift', 'jam_mulai', 'jam_selesai', 'lokasi_pos', 'status', 'created_by'];

    protected $casts = ['tanggal' => 'date'];

    public function petugas()
    {
        return $this->hasMany(PetugasRonda::class);
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiRonda::class);
    }

    public function getJamShiftFormattedAttribute(): string
    {
        if ($this->jam_mulai && $this->jam_selesai) {
            return "{$this->jam_mulai} - {$this->jam_selesai} WIB";
        }
        return '22:00 - 04:00 WIB';
    }
}
