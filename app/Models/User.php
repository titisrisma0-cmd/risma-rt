<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'rt_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function managedRt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function isAdminRw(): bool
    {
        return in_array(optional($this->role)->nama_role, ['admin', 'rw', 'admin_rw'], true);
    }

    public function isAdminRt(): bool
    {
        return in_array(optional($this->role)->nama_role, ['rt', 'admin_rt'], true);
    }

    public function managedRtId(): ?int
    {
        $rtId = $this->rt_id ?? optional($this->profile)->rt_id;

        return $rtId === null ? null : (int) $rtId;
    }

    public function hasRole($role)
    {
        return $this->role && $this->role->nama_role === $role;
    }

    public function kegiatanPeserta()
    {
        return $this->hasMany(KegiatanPeserta::class);
    }
    
    protected static function booted()
    {
        static::created(function ($user) {
            UserProfile::create([
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'nama_lengkap' => $user->name,
                'status' => 'aktif'
            ]);
        });
    }
}
