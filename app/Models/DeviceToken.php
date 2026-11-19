<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token_hash',
        'is_active',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung hash SHA-256 dari plain token
     */
    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', trim($plainToken));
    }

    /**
     * Cari device token berdasarkan plain token menggunakan hash_equals
     */
    public static function findByPlainToken(?string $plainToken): ?self
    {
        if (empty($plainToken)) {
            return null;
        }

        $hash = self::hashToken($plainToken);

        $candidate = self::where('token_hash', $hash)->first();

        if ($candidate && hash_equals($candidate->token_hash, $hash)) {
            return $candidate;
        }

        return null;
    }
}
