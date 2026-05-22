<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id',
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

    public function hasRole($role)
    {
        return $this->role && $this->role->nama_role === $role;
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