<?php
namespace App\Http\Controllers;

use App\Models\Aspirasi;
use App\Models\AspirasiLampiran;
use App\Models\AspirasiTanggapan;
use Illuminate\Http\Request;

class AspirasiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('profile.kartuKeluarga');
        $query = Aspirasi::with(['pelapor.profile','lampirans','tanggapans.user','rt'])->latest();
        $this->scope($query, $user);
        $aspirasis = $query->when($request->kategori, fn($q,$v)=>$q->where('kategori',$v))->when($request->status, fn($q,$v)=>$q->where('status',$v))->get();
        $aspirasis->each(fn($a) => $this->hideIdentity($a, $user));
        if ($request->expectsJson()) return response()->json($aspirasis);
        $canManage = $this->isRw($user) || $this->isRt($user);
        $rekap = $canManage ? ['total' => $aspirasis->count(), 'diproses' => $aspirasis->whereIn('status', ['diproses', 'ditanggapi'])->count(), 'selesai' => $aspirasis->where('status', 'selesai')->count()] : null;
        return view('layanan.aspirasi', compact('aspirasis', 'canManage', 'rekap'));
    }

    public function store(Request $request)
    {
        $user=$request->user()->loadMissing('profile');
        $data=$request->validate(['judul'=>'required|string|max:255','deskripsi'=>'required|string','kategori'=>'required|in:infrastruktur,kebersihan,keamanan,sosial,keluhan_pelayanan,usulan,lainnya','anonim'=>'nullable|boolean','lokasi'=>'nullable|string|max:255','latitude'=>'nullable|numeric','longitude'=>'nullable|numeric','foto.*'=>'nullable|image|max:4096']);
        $a=Aspirasi::create($data + ['user_id'=>$user->id,'rt_id'=>$user->profile?->rt_id,'rw_id'=>$user->profile?->rw_id,'kartu_keluarga_id'=>$user->profile?->kartu_keluarga_id,'anonim'=>$request->boolean('anonim'),'status'=>'terkirim']);
        foreach($request->file('foto',[]) as $file) AspirasiLampiran::create(['aspirasi_id'=>$a->id,'file_path'=>$file->store('aspirasi','public'),'file_type'=>$file->getClientMimeType()]);
        return $this->respond($request,$a,'Aspirasi berhasil dikirim.',201);
    }

    public function show(Request $request, Aspirasi $aspirasi)
    {
        $user=$request->user()->loadMissing('profile.kartuKeluarga'); abort_unless($this->canSee($user,$aspirasi),403);
        $aspirasi->load(['pelapor.profile','lampirans','tanggapans.user']); $this->hideIdentity($aspirasi,$user);
        return $request->expectsJson()?response()->json($aspirasi):view('layanan.aspirasi-detail',compact('aspirasi'));
    }

    public function tanggapan(Request $request, Aspirasi $aspirasi)
    {
        $user=$request->user()->loadMissing('profile'); abort_unless($this->canManage($user,$aspirasi),403);
        $data=$request->validate(['isi'=>'required|string']); $t=AspirasiTanggapan::create($data+['aspirasi_id'=>$aspirasi->id,'user_id'=>$user->id]);
        if($aspirasi->status==='terkirim') $aspirasi->update(['status'=>'ditanggapi','assigned_to'=>$user->id]);
        return $this->respond($request,$t,'Tanggapan berhasil dikirim.',201);
    }

    public function status(Request $request, Aspirasi $aspirasi)
    {
        $user=$request->user()->loadMissing('profile'); abort_unless($this->canManage($user,$aspirasi),403);
        $data=$request->validate(['status'=>'required|in:terkirim,diproses,ditanggapi,dieskalasi,selesai,ditolak','solusi'=>'nullable|string']);
        $aspirasi->update($data+['assigned_to'=>$user->id,'resolved_at'=>in_array($data['status'],['selesai','ditolak'])?now():null]);
        return $this->respond($request,$aspirasi->fresh(),'Status aspirasi diperbarui.');
    }

    public function eskalasi(Request $request, Aspirasi $aspirasi)
    {
        $user=$request->user()->loadMissing('profile'); abort_unless($this->isRt($user)||$this->isRw($user),403); abort_unless($this->canManage($user,$aspirasi),403);
        $data=$request->validate(['target'=>'nullable|string|max:255']); $aspirasi->update(['status'=>'dieskalasi','escalated_at'=>now(),'escalation_target'=>$data['target']??($this->isRt($user)?'RW':'Instansi luar')]);
        return $this->respond($request,$aspirasi->fresh(),'Aspirasi berhasil dieskalasi.');
    }

    public function rekap(Request $request)
    {
        $user=$request->user()->loadMissing('profile'); abort_unless($this->isRw($user)||$this->isRt($user),403);
        $q=Aspirasi::query(); if($this->isRt($user)&&!$this->isRw($user))$q->where('rt_id',$user->profile?->rt_id);
        $items=$q->get(); $data=['total'=>$items->count(),'per_kategori'=>$items->countBy('kategori'),'per_status'=>$items->countBy('status'),'rata_rata_jam_selesai'=>round($items->filter(fn($a)=>$a->resolved_at)->avg(fn($a)=>$a->created_at->diffInMinutes($a->resolved_at))/60,1)];
        return $request->expectsJson()?response()->json($data):response()->json($data);
    }

    private function scope($q,$u){ if($this->isRw($u)) return; if($this->isRt($u)){ $q->where('rt_id',$u->profile?->rt_id); return;} $kk=$u->profile?->kartu_keluarga_id; $q->where(fn($x)=>$x->where('user_id',$u->id)->orWhere('kartu_keluarga_id',$kk)); }
    private function canSee($u,$a){ if($this->isRw($u))return true; if($this->isRt($u))return $a->rt_id===$u->profile?->rt_id; return $a->user_id===$u->id||($u->profile?->kartu_keluarga_id&&$a->kartu_keluarga_id===$u->profile->kartu_keluarga_id); }
    private function canManage($u,$a){ return ($this->isRw($u)||$this->isRt($u))&&$this->canSee($u,$a); }
    private function isRw($u){return $u->hasRole('rw')||$u->hasRole('admin');} private function isRt($u){return $u->hasRole('rt');}
    private function hideIdentity($a,$u){ if($a->anonim&&$this->isRt($u)&&!$this->isRw($u)){ $a->setRelation('pelapor',null); $a->makeHidden(['user_id','kartu_keluarga_id']); } }
    private function respond($r,$data,$message,$status=200){return $r->expectsJson()?response()->json($data,$status):redirect()->route('aspirasi.index')->with('success',$message);}
}
