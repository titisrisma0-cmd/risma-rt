<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiWarga extends Model
{
    use HasFactory;

    protected $table = 'mutasi_wargas';

    protected $fillable = [
        'rt_id',
        'user_profile_id',
        'nama_warga',
        'nik',
        'no_kk',
        'jenis_mutasi',
        'tanggal_mutasi',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
