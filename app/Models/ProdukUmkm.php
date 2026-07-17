<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukUmkm extends Model
{
    protected $fillable = [
        'umkm_id', 'nama_produk', 'kategori', 'deskripsi', 'harga', 'stok',
        'satuan', 'foto', 'status', 'moderated_by', 'moderated_at', 'catatan_moderasi',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'moderated_at' => 'datetime',
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }
}
