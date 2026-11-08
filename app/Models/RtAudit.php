<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtAudit extends Model
{
    use HasFactory;

    protected $table = 'rt_audits';

    protected $fillable = [
        'rt_id',
        'tahun',
        'bulan',
        'total_pemasukan',
        'total_pengeluaran',
        'saldo_kas',
        'status',
        'catatan_rw',
        'audited_by',
        'audited_at',
    ];

    protected $casts = [
        'total_pemasukan' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
        'saldo_kas' => 'decimal:2',
        'audited_at' => 'datetime',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'sedang_diperiksa' => 'Sedang Diperiksa',
            'perlu_perbaikan' => 'Perlu Perbaikan',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak dengan Catatan',
            default => 'Belum Diperiksa',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'sedang_diperiksa' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'perlu_perbaikan' => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
            'terverifikasi' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            'ditolak' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        };
    }
}
