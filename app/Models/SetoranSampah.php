<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetoranSampah extends Model
{
    protected $fillable = [
        'user_id', 'jenis_sampah_id', 'berat_kg', 'harga_per_kg', 'total_saldo',
        'status', 'created_by', 'validated_by', 'validated_at', 'catatan', 'tanggal_setor', 'foto_bukti',
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_saldo' => 'decimal:2',
        'validated_at' => 'datetime',
        'tanggal_setor' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
