<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasRonda extends Model
{
    protected $fillable = ['jadwal_ronda_id', 'user_id', 'user_profile_id', 'peran'];

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

    public function getNamaPetugasAttribute(): string
    {
        return $this->userProfile?->nama_lengkap ?? $this->user?->name ?? 'Petugas';
    }

    public function getHasAccountAttribute(): bool
    {
        return !empty($this->user_id) || !empty($this->userProfile?->user_id);
    }
}
