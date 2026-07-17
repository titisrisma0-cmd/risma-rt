<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspirasiLampiran extends Model
{
    protected $fillable = ['aspirasi_id', 'file_path', 'file_type'];

    public function aspirasi()
    {
        return $this->belongsTo(Aspirasi::class);
    }
}
