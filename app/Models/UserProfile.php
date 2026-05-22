<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    
    protected $fillable = [
        'user_id', 'role_id', 
        'no_kk', 'nik', 'nama_lengkap',
        'tempat_lahir', 'tanggal_lahir', 
        'jenis_kelamin', 'agama',
        'alamat', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota',
        'no_hp', 'pekerjaan', 
        'status_perkawinan', 'pendidikan',
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
}