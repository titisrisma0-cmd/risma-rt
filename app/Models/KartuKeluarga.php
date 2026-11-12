<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuKeluarga extends Model
{
    protected $fillable = ['rt_id', 'no_kk', 'kepala_keluarga_user_id', 'alamat', 'status_rumah'];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function kepalaKeluarga()
    {
        return $this->belongsTo(User::class, 'kepala_keluarga_user_id');
    }

    public function anggota()
    {
        return $this->hasMany(UserProfile::class, 'kartu_keluarga_id');
    }

    public function anggotaAktif()
    {
        return $this->hasMany(UserProfile::class, 'kartu_keluarga_id')->where('status', 'aktif');
    }

    public function balitas()
    {
        return $this->hasMany(Balita::class, 'kartu_keluarga_id');
    }

    public function aspirasis()
    {
        return $this->hasMany(Aspirasi::class, 'kartu_keluarga_id');
    }

    public function getNamaKepalaKeluargaAttribute(): string
    {
        if ($this->kepalaKeluarga) {
            return $this->kepalaKeluarga->name;
        }

        $kepala = $this->anggota->firstWhere('status_hubungan_keluarga', 'Kepala Keluarga');
        return $kepala ? $kepala->nama_lengkap : 'Belum Ditentukan';
    }

    public function getJumlahJiwaAttribute(): int
    {
        return $this->anggota()->where('status', 'aktif')->count();
    }

    public function getJumlahLakiLakiAttribute(): int
    {
        return $this->anggota()->where('status', 'aktif')->where('jenis_kelamin', 'L')->count();
    }

    public function getJumlahPerempuanAttribute(): int
    {
        return $this->anggota()->where('status', 'aktif')->where('jenis_kelamin', 'P')->count();
    }
}
