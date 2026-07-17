<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JadwalBankSampah extends Model { protected $fillable=['rt_id','judul','waktu','lokasi','keterangan','aktif']; protected $casts=['waktu'=>'datetime','aktif'=>'boolean']; public function rt(){return $this->belongsTo(Rt::class);} }
