<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $fillable = [
        'rt_id', 'rw_id', 'tanggal', 'kategori', 'nominal', 'keterangan', 'bukti', 'nota_path', 'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
