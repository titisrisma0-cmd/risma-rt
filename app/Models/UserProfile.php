<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    
    protected $fillable = [
        'user_id', 'role_id', 'rw_id', 'rt_id', 'kartu_keluarga_id',
        'no_kk', 'nik', 'nama_lengkap',
        'tempat_lahir', 'tanggal_lahir', 
        'jenis_kelamin', 'agama',
        'alamat', 'nomor_rumah', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota',
        'no_hp', 'pekerjaan', 
        'status_perkawinan', 'status_hubungan_keluarga', 'pendidikan',
        'nama_ayah', 'nama_ibu', 
        'foto', 'foto_base64', 'status'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function wilayahRw()
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function wilayahRt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function kartuKeluarga()
    {
        return $this->belongsTo(KartuKeluarga::class);
    }
}
