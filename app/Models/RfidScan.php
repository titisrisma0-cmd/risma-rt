<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidScan extends Model
{
    use HasFactory;

    protected $table = 'rfid_scans';

    protected $fillable = [
        'uid',
        'formatted_uid',
        'card_type',
        'sak',
        'atqa',
        'uid_size',
        'device_id',
        'fungsi',
        'device_token_id',
        'user_profile_id',
        'rt_id',
        'nama_warga',
        'access_granted',
        'buzzer_signal',
        'access_message',
        'ip_address',
        'scanned_at',
    ];

    protected $casts = [
        'access_granted' => 'boolean',
        'uid_size' => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function deviceToken()
    {
        return $this->belongsTo(DeviceToken::class, 'device_token_id');
    }

    public function device()
    {
        return $this->belongsTo(RfidDevice::class, 'device_id', 'device_id');
    }

    /**
     * Scope riwayat scan berdasarkan wilayah peran user (Super Admin / RW = semua, RT = hanya RT binaannya)
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdminRt()) {
            $managedRtId = $user->managedRtId();

            if ($managedRtId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('rt_id', $managedRtId);
        }

        return $query;
    }
}
