<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiRonda extends Model
{
    protected $fillable = [
        'jadwal_ronda_id', 'user_id', 'user_profile_id', 'status', 'waktu_absen',
        'foto_bukti', 'lokasi_lat', 'lokasi_lng', 'catatan', 'recorded_by',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
        'lokasi_lat' => 'decimal:7',
        'lokasi_lng' => 'decimal:7',
    ];

    public function jadwalRonda()
    {
        return $this->belongsTo(JadwalRonda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }
}
