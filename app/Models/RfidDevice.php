<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidDevice extends Model
{
    use HasFactory;

    protected $table = 'rfid_devices';

    public const FUNGSI_GERBANG = 'gerbang';
    public const FUNGSI_BANK_SAMPAH = 'bank_sampah';
    public const FUNGSI_PRESENSI = 'presensi';
    public const FUNGSI_PEREKAMAN = 'perekaman';
    public const FUNGSI_BELUM_DIKONFIGURASI = 'belum_dikonfigurasi';

    public const VALID_FUNCTIONS = [
        self::FUNGSI_GERBANG,
        self::FUNGSI_BANK_SAMPAH,
        self::FUNGSI_PRESENSI,
        self::FUNGSI_PEREKAMAN,
        self::FUNGSI_BELUM_DIKONFIGURASI,
    ];

    protected $fillable = [
        'device_id',
        'nama_lokasi',
        'fungsi',
        'is_active',
        'rt_id',
        'keterangan',
        'last_seen_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scans()
    {
        return $this->hasMany(RfidScan::class, 'device_id', 'device_id');
    }

    public function isConfigured(): bool
    {
        return $this->fungsi !== self::FUNGSI_BELUM_DIKONFIGURASI;
    }

    public function getFungsiLabelAttribute(): string
    {
        return match ($this->fungsi) {
            self::FUNGSI_GERBANG => 'Pintu Gerbang / Portal',
            self::FUNGSI_BANK_SAMPAH => 'Setoran Bank Sampah',
            self::FUNGSI_PRESENSI => 'Presensi Kehadiran / Ronda',
            self::FUNGSI_PEREKAMAN => 'Perekaman E-KTP / Kartu',
            default => 'Belum Dikonfigurasi',
        };
    }

    public function getFungsiBadgeClassAttribute(): string
    {
        return match ($this->fungsi) {
            self::FUNGSI_GERBANG => 'bg-primary text-white',
            self::FUNGSI_BANK_SAMPAH => 'bg-success text-white',
            self::FUNGSI_PRESENSI => 'bg-warning text-dark',
            self::FUNGSI_PEREKAMAN => 'bg-purple text-white',
            default => 'bg-secondary text-white',
        };
    }
}
