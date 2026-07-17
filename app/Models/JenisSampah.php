<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    protected $fillable = ['nama', 'kategori', 'harga_per_kg', 'status'];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function setorans()
    {
        return $this->hasMany(SetoranSampah::class);
    }
}
