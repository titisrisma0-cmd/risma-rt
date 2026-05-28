<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
        'no_transaksi', 'user_id', 'iuran_id', 'tanggal_bayar',
        'jumlah_bayar', 'denda', 'metode_bayar', 'bukti_transfer', 'status', 
        'bulan_tahun', 'keterangan', 'verified_by', 'verified_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function iuran()
    {
        return $this->belongsTo(Iuran::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    
    public function getBuktiTransferUrlAttribute()
    {
        if ($this->bukti_transfer && file_exists(public_path('storage/' . $this->bukti_transfer))) {
            return asset('storage/' . $this->bukti_transfer);
        }
        return null;
    }
}