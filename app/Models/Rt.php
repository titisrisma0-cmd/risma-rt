<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rt extends Model
{
    protected $fillable = ['rw_id', 'kode_rt', 'nama_rt', 'ketua_user_id'];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    public function kartuKeluargas()
    {
        return $this->hasMany(KartuKeluarga::class);
    }

    public function wargaProfiles()
    {
        return $this->hasMany(UserProfile::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class);
    }

    public function cctvCameras()
    {
        return $this->hasMany(CctvCamera::class);
    }
}
