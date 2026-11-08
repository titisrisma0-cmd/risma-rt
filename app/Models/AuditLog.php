<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'rt_id',
        'action',
        'entity_type',
        'entity_id',
        'deskripsi',
        'nominal',
        'bukti',
        'ip_address',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public static function record(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $deskripsi = null,
        ?float $nominal = null,
        ?string $bukti = null,
        ?int $rtId = null,
        ?int $userId = null
    ): self {
        $user = auth()->user();
        $finalUserId = $userId ?? ($user ? $user->id : null);
        $finalRtId = $rtId ?? ($user ? $user->managedRtId() : null);

        return static::create([
            'user_id' => $finalUserId,
            'rt_id' => $finalRtId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'deskripsi' => $deskripsi,
            'nominal' => $nominal,
            'bukti' => $bukti,
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);
    }
}
