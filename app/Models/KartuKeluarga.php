<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuKeluarga extends Model
{
    protected $fillable = ['rt_id', 'no_kk', 'kepala_keluarga_user_id', 'alamat', 'status_rumah'];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function kepalaKeluarga()
    {
        return $this->belongsTo(User::class, 'kepala_keluarga_user_id');
    }

    public function anggota()
    {
        return $this->hasMany(UserProfile::class);
    }
}
