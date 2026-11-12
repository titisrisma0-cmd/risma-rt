<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    
    protected $fillable = [
        'user_id', 'role_id', 'rw_id', 'rt_id', 'kartu_keluarga_id',
        'no_kk', 'nik', 'nama_lengkap',
        'tempat_lahir', 'tanggal_lahir', 
        'jenis_kelamin', 'agama',
        'alamat', 'nomor_rumah', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota',
        'no_hp', 'pekerjaan', 
        'status_perkawinan', 'status_hubungan_keluarga', 'pendidikan',
        'nama_ayah', 'nama_ibu', 
        'foto', 'foto_base64', 'status',
        'siap_ronda', 'alasan_pengecualian_ronda',
        'catatan_verifikasi', 'verified_by', 'verified_at'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'siap_ronda' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAgeAttribute(): ?int
    {
        if (! $this->tanggal_lahir) {
            return null;
        }

        if ($this->tanggal_lahir instanceof \Carbon\CarbonInterface) {
            return $this->tanggal_lahir->age;
        }

        try {
            return \Carbon\Carbon::parse($this->tanggal_lahir)->age;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->nama_lengkap ?? optional($this->user)->name ?? '');
    }

    public function getProfileAttribute(): self
    {
        return $this;
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function petugasRondas()
    {
        return $this->hasMany(PetugasRonda::class, 'user_profile_id');
    }

    public function absensiRondas()
    {
        return $this->hasMany(AbsensiRonda::class, 'user_profile_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeEligibleRonda($query)
    {
        return $query->where('status', 'aktif')
            ->where('jenis_kelamin', 'L')
            ->where(function ($q) {
                $q->whereNull('siap_ronda')
                  ->orWhere('siap_ronda', true);
            });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function wilayahRw()
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function wilayahRt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function kartuKeluarga()
    {
        return $this->belongsTo(KartuKeluarga::class);
    }
}
