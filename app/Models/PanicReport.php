<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanicReport extends Model
{
    protected $fillable = [
        'user_id', 'rt_id', 'judul', 'deskripsi', 'lokasi_lat', 'lokasi_lng',
        'status', 'handled_by', 'handled_at',
    ];

    protected $casts = [
        'lokasi_lat' => 'decimal:7',
        'lokasi_lng' => 'decimal:7',
        'handled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
