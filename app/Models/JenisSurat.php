<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $fillable = ['kode', 'nama', 'field_form', 'persyaratan', 'template', 'status'];

    protected $casts = [
        'field_form' => 'array',
        'persyaratan' => 'array',
        'status' => 'boolean',
    ];

    public function pengajuan()
    {
        return $this->hasMany(PengajuanSurat::class);
    }
}
