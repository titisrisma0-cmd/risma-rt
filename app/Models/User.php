<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'is_super_admin', 'rt_id', 'jenis_kelamin',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
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

    public function getAgeAttribute(): ?int
    {
        $ttl = $this->tanggal_lahir ?? optional($this->profile)->tanggal_lahir;
        if (! $ttl) {
            return null;
        }

        if ($ttl instanceof \Carbon\CarbonInterface) {
            return $ttl->age;
        }

        try {
            return \Carbon\Carbon::parse($ttl)->age;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getNamaLengkapAttribute(): string
    {
        return (string) (optional($this->profile)->nama_lengkap ?? $this->name ?? '');
    }

    public function getSiapRondaAttribute(): bool
    {
        return (bool) (optional($this->profile)->siap_ronda ?? true);
    }

    public function getStatusHubunganKeluargaAttribute(): ?string
    {
        return optional($this->profile)->status_hubungan_keluarga;
    }

    public function petugasRondas()
    {
        return $this->hasMany(PetugasRonda::class, 'user_id');
    }

    public function managedRt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) (
            $this->is_super_admin
            || optional($this->role)->nama_role === 'super_admin'
            || in_array($this->email, ['titisrisma0@gmail.com', 'admin@rw-smart.test'], true)
        );
    }

    public function isSimulatingRole(): bool
    {
        if (! $this->isSuperAdmin()) {
            return false;
        }

        $ctx = $this->getActiveContext();
        return ! empty($ctx) && ! empty($ctx['mode']) && $ctx['mode'] !== 'super_admin';
    }

    public function getActiveContext(): ?array
    {
        if (! $this->isSuperAdmin()) {
            return null;
        }

        return session('super_admin_context');
    }

    public function isAdminRw(): bool
    {
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            if ($ctx) {
                return ($ctx['mode'] ?? '') === 'rw';
            }
            return true; // Super Admin mode has full RW authority
        }

        return in_array(optional($this->role)->nama_role, ['admin', 'rw', 'admin_rw'], true);
    }

    public function isAdminRt(): bool
    {
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            return ($ctx['mode'] ?? '') === 'rt';
        }

        $role = optional($this->role)->nama_role;
        return in_array($role, ['rt', 'admin_rt', 'ketua_rt'], true)
            || (bool) preg_match('/^rt_?[0-9]+/i', (string) $role);
    }

    public function managedRtId(): ?int
    {
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            if ($ctx && in_array($ctx['mode'] ?? '', ['rt', 'warga'], true)) {
                if (! empty($ctx['rt_id'])) {
                    return (int) $ctx['rt_id'];
                }
            }
            if (! $ctx || ($ctx['mode'] ?? '') === 'super_admin' || ($ctx['mode'] ?? '') === 'rw') {
                return null;
            }
        }

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
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            if ($ctx && ! empty($ctx['rt_id'])) {
                $rt = Rt::find($ctx['rt_id']);
                if ($rt && $rt->kode_rt) {
                    return $rt->kode_rt;
                }
            }
            if (! $this->isSimulatingRole()) {
                return null;
            }
        }

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
        if ($this->isSuperAdmin()) {
            if ($this->isSimulatingRole()) {
                $ctx = $this->getActiveContext();
                return $ctx['label'] ?? 'Mode Simulasi';
            }
            return 'Super Admin';
        }

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
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            if ($ctx && ! empty($ctx['mode'])) {
                if ($ctx['mode'] === 'rt') {
                    return in_array($role, ['rt', 'admin_rt', 'ketua_rt'], true);
                }
                if ($ctx['mode'] === 'warga') {
                    return $role === 'warga';
                }
                if ($ctx['mode'] === 'rw') {
                    return in_array($role, ['rw', 'admin', 'admin_rw'], true);
                }
            }
            if (in_array($role, ['super_admin', 'rw', 'admin', 'admin_rw'], true)) {
                return true;
            }
            return false;
        }

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
        if ($this->isSuperAdmin()) {
            $ctx = $this->getActiveContext();
            return ($ctx['mode'] ?? '') === 'warga';
        }

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
