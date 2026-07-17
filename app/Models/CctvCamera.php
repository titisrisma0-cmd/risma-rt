<?php

namespace App\Models;

use App\Models\Scopes\RtCctvScope;
use Illuminate\Database\Eloquent\Model;

class CctvCamera extends Model
{
    protected $fillable = ['rt_id', 'nama_lokasi', 'stream_url', 'status', 'latitude', 'longitude'];

    protected $casts = ['latitude' => 'float', 'longitude' => 'float'];

    protected static function booted(): void
    {
        static::addGlobalScope(new RtCctvScope);
    }

    public function rt() { return $this->belongsTo(Rt::class); }
    public function recordings() { return $this->hasMany(CctvRecording::class, 'camera_id'); }
}
