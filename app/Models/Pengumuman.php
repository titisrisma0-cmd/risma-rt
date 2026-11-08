<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'target_audience',
        'rt_id',
        'tanggal_publikasi',
        'status',
        'lampiran',
        'created_by',
    ];

    protected $casts = [
        'tanggal_publikasi' => 'datetime',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_audience) {
            'rt_tertentu' => 'Pengurus RT ' . ($this->rt ? $this->rt->kode_rt : ''),
            'seluruh_warga' => 'Seluruh Warga RW',
            'warga_rt' => 'Warga RT ' . ($this->rt ? $this->rt->kode_rt : ''),
            default => 'Semua Pengurus RT',
        };
    }
}
