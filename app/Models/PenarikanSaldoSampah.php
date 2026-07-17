<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenarikanSaldoSampah extends Model
{
    protected $fillable = [
        'user_id', 'nominal', 'metode', 'rekening_tujuan', 'status',
        'processed_by', 'processed_at', 'catatan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
