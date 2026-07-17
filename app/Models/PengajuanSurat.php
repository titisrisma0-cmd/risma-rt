<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanSurat extends Model
{
    protected $fillable = [
        'user_id', 'jenis_surat_id', 'rt_id', 'nomor_pengajuan', 'nomor_surat',
        'keperluan', 'data_form_json', 'berkas_pendukung', 'status', 'approved_by',
        'approved_at', 'catatan', 'file_pdf',
    ];

    protected $casts = [
        'data_form_json' => 'array',
        'berkas_pendukung' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
