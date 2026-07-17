<?php

namespace App\Models;

use App\Models\Scopes\RtCctvScope;
use Illuminate\Database\Eloquent\Model;

class CctvRecording extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['camera_id', 'file_path', 'tanggal_rekaman', 'jam_mulai', 'jam_selesai', 'durasi_detik', 'ukuran_file_mb'];
    protected $casts = ['tanggal_rekaman' => 'date', 'ukuran_file_mb' => 'decimal:2'];

    protected static function booted(): void
    {
        static::addGlobalScope(new RtCctvScope);
    }

    public function camera() { return $this->belongsTo(CctvCamera::class, 'camera_id'); }
}
