<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'rt_id', 'jenis_kelamin',
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
        $role = optional($this->role)->nama_role;
        return in_array($role, ['rt', 'admin_rt', 'ketua_rt'], true)
            || (bool) preg_match('/^rt_?[0-9]+/i', (string) $role);
    }

    public function managedRtId(): ?int
    {
        $rtId = $this->rt_id ?? optional($this->profile)->rt_id;

        if ($rtId !== null) {
            return (int) $rtId;
        }

        $role = optional($this->role)->nama_role;
        if (preg_match('/^rt_?0*([0-9]+)$/i', (string) $role, $m)) {
            $kode = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $rt = Rt::where('kode_rt', $kode)->first();
            if ($rt) {
                return (int) $rt->id;
            }
        }

        return null;
    }

    public function getRtKode(): ?string
    {
        $rtId = $this->managedRtId();
        if ($rtId) {
            $rt = $this->managedRt ?? Rt::find($rtId);
            if ($rt && $rt->kode_rt) {
                return $rt->kode_rt;
            }
        }

        $role = optional($this->role)->nama_role;
        if (preg_match('/^rt_?0*([0-9]+)$/i', (string) $role, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        return null;
    }

    public function getRoleDisplayName(): string
    {
        if ($this->isAdminRt()) {
            $kode = $this->getRtKode();
            return $kode ? 'Ketua RT ' . $kode : 'Ketua RT';
        }

        if ($this->isAdminRw()) {
            return 'Pengurus RW';
        }

        if ($this->hasRole('bendahara')) {
            return 'Bendahara RW';
        }

        return ucfirst(optional($this->role)->nama_role ?? 'Warga');
    }

    public function hasRole($role)
    {
        if (! $this->role) {
            return false;
        }

        if ($this->role->nama_role === $role) {
            return true;
        }

        if ($role === 'rt' && $this->isAdminRt()) {
            return true;
        }

        if (($role === 'rw' || $role === 'admin') && $this->isAdminRw()) {
            return true;
        }

        return false;
    }

    public function isWarga(): bool
    {
        return ! $this->isAdminRw() && ! $this->isAdminRt() && ! $this->hasRole('bendahara');
    }

    public function getKartuKeluargaAttribute(): ?KartuKeluarga
    {
        return optional($this->profile)->kartuKeluarga;
    }

    public function kegiatanPeserta()
    {
        return $this->hasMany(KegiatanPeserta::class);
    }
    
    protected static function booted()
    {
        static::created(function ($user) {
            $data = [
                'role_id' => $user->role_id,
                'nama_lengkap' => $user->name,
                'status' => 'aktif',
            ];

            if ($user->rt_id !== null && \Illuminate\Support\Facades\Schema::hasColumn('user_profiles', 'rt_id')) {
                $data['rt_id'] = $user->rt_id;
            }

            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                $data
            );
        });
    }
}
