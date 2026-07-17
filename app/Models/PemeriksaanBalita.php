<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanBalita extends Model
{
    protected $fillable = [
        'balita_id', 'jadwal_posyandu_id', 'tanggal_periksa', 'berat_kg',
        'tinggi_cm', 'lingkar_kepala_cm', 'imunisasi_sudah', 'imunisasi_belum',
        'catatan', 'petugas_id',
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
        'berat_kg' => 'decimal:2',
        'tinggi_cm' => 'decimal:2',
        'lingkar_kepala_cm' => 'decimal:2',
        'imunisasi_sudah' => 'array',
        'imunisasi_belum' => 'array',
    ];

    public function balita()
    {
        return $this->belongsTo(Balita::class);
    }
}
