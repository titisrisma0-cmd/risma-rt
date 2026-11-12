<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukarJadwalRonda extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'user_id',
        'jenis_pengajuan',
        'jadwal_ronda_asal_id',
        'target_user_id',
        'target_jadwal_ronda_id',
        'alasan',
        'status',
        'catatan_rt',
        'approved_by',
        'approved_at',
        'target_user_approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'target_user_approved_at' => 'datetime',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function jadwalRondaAsal()
    {
        return $this->belongsTo(JadwalRonda::class, 'jadwal_ronda_asal_id');
    }

    public function targetJadwalRonda()
    {
        return $this->belongsTo(JadwalRonda::class, 'target_jadwal_ronda_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
