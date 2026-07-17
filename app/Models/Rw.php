<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rw extends Model
{
    protected $fillable = ['kode_rw', 'nama_rw', 'ketua_user_id'];

    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    public function rts()
    {
        return $this->hasMany(Rt::class);
    }

    public function wargaProfiles()
    {
        return $this->hasManyThrough(UserProfile::class, Rt::class, 'rw_id', 'rt_id');
    }
}
