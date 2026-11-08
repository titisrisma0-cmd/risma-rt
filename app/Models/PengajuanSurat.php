<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanSurat extends Model
{
    protected $fillable = [
        'user_id', 'jenis_surat_id', 'rt_id', 'target_level', 'kebutuhan_rw',
        'nomor_pengajuan', 'nomor_surat', 'keperluan', 'data_form_json',
        'berkas_pendukung', 'status', 'approved_by', 'approved_at',
        'catatan', 'catatan_rt', 'catatan_rw',
        'rt_approved_by', 'rt_approved_at',
        'rw_approved_by', 'rw_approved_at',
        'file_pdf',
    ];

    protected $casts = [
        'data_form_json' => 'array',
        'berkas_pendukung' => 'array',
        'approved_at' => 'datetime',
        'rt_approved_at' => 'datetime',
        'rw_approved_at' => 'datetime',
        'kebutuhan_rw' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rtApprover()
    {
        return $this->belongsTo(User::class, 'rt_approved_by');
    }

    public function rwApprover()
    {
        return $this->belongsTo(User::class, 'rw_approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending', 'menunggu_rt' => 'Menunggu Verifikasi RT',
            'diproses', 'diproses_rt' => 'Sedang Diproses RT',
            'menunggu_rw', 'menunggu_persetujuan_rw' => 'Menunggu Persetujuan RW',
            'siap_diambil', 'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending', 'menunggu_rt' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'diproses', 'diproses_rt' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            'menunggu_rw', 'menunggu_persetujuan_rw' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            'siap_diambil', 'disetujui' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            'ditolak' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            'selesai' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        };
    }
}
