<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';
    
    protected $fillable = [
        'tanggal', 'jenis', 'nominal', 'sumber', 'keterangan', 'pembayaran_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relasi ke Pembayaran
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }
}