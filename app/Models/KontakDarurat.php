<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontakDarurat extends Model
{
    protected $fillable = ['nama', 'kategori', 'nomor_telepon', 'alamat', 'status'];

    protected $casts = ['status' => 'boolean'];
}
