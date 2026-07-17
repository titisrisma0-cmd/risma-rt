<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoSampah extends Model
{
    protected $fillable = ['user_id', 'saldo', 'total_berat_kg'];

    protected $casts = [
        'saldo' => 'decimal:2',
        'total_berat_kg' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
