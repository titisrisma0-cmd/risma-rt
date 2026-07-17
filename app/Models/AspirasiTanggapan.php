<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AspirasiTanggapan extends Model { protected $fillable = ['aspirasi_id','user_id','isi']; public function user(){ return $this->belongsTo(User::class); } public function aspirasi(){ return $this->belongsTo(Aspirasi::class); } }
