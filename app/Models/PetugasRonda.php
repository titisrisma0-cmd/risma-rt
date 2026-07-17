<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasRonda extends Model
{
    protected $fillable = ['jadwal_ronda_id', 'user_id', 'peran'];

    public function jadwalRonda()
    {
        return $this->belongsTo(JadwalRonda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
