<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Umkm extends Model
{
    protected $fillable = [
        'user_id', 'nama_usaha', 'kategori', 'deskripsi', 'alamat', 'whatsapp',
        'foto_toko', 'status', 'moderated_by', 'moderated_at', 'catatan_moderasi',
    ];

    protected $casts = ['moderated_at' => 'datetime'];

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produk()
    {
        return $this->hasMany(ProdukUmkm::class);
    }
}
