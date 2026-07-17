<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalRonda extends Model
{
    protected $fillable = ['rt_id', 'tanggal', 'shift', 'lokasi_pos', 'status', 'created_by'];

    protected $casts = ['tanggal' => 'date'];

    public function petugas()
    {
        return $this->hasMany(PetugasRonda::class);
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiRonda::class);
    }
}
