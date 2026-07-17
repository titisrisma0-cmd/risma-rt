<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';
    
    protected $fillable = [
        'rt_id', 'rw_id', 'tanggal', 'jenis', 'nominal', 'sumber', 'keterangan', 'pembayaran_id', 'created_by'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relasi ke Pembayaran
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
