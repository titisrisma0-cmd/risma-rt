<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspirasiKomentar extends Model
{
    protected $fillable = ['aspirasi_id', 'user_id', 'komentar', 'is_internal'];

    protected $casts = ['is_internal' => 'boolean'];

    public function aspirasi()
    {
        return $this->belongsTo(Aspirasi::class);
    }
}
