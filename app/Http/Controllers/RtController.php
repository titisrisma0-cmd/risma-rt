<?php

namespace App\Http\Controllers;

use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RtController extends Controller
{
    public function index()
    {
        $this->ensureAdminRw();

        $rts = Rt::with([
                'rw',
                'wargaProfiles:id,rt_id,user_id,kartu_keluarga_id,no_kk,nik,nama_lengkap',
                'wargaProfiles.user:id,name',
                'wargaProfiles.kartuKeluarga.kepalaKeluarga:id,name',
            ])
                ->withCount('wargaProfiles')
                ->orderBy('rw_id')->orderBy('kode_rt')->get();

        $rts->each(function (Rt $rt) {
            $keluarga = $rt->wargaProfiles
                ->groupBy(fn ($profile) => blank($profile->no_kk)
                    ? 'PERSON:'.($profile->nik ?: $profile->user_id)
                    : trim($profile->no_kk))
                ->map(function ($anggota, $noKk) {
                    $first = $anggota->first();

                    return [
                        'no_kk' => str_starts_with($noKk, 'PERSON:') ? '-' : $noKk,
                        'kepala_keluarga' => $first->kartuKeluarga?->kepalaKeluarga?->name
                            ?: $first->nama_lengkap
                            ?: $first->user?->name,
                        'anggota' => $anggota
                            ->map(fn ($profile) => $profile->nama_lengkap ?: $profile->user?->name)
                            ->filter()->sort()->values(),
                    ];
                })->sortBy('no_kk')->values();

            $rt->setAttribute('keluarga_count', $keluarga->count());
            $rt->setAttribute('keluarga', $keluarga);
        });

        return view('rts.index', [
            'rts' => $rts,
            'rws' => Rw::orderBy('kode_rw')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdminRw();
        $data = $request->validate([
            'rw_id' => 'required|exists:rws,id',
            'kode_rt' => 'required|string|max:10',
            'nama_rt' => 'nullable|string|max:255',
        ]);
        $request->validate(['kode_rt' => 'unique:rts,kode_rt,NULL,id,rw_id,'.$data['rw_id']]);
        Rt::create($data);

        return back()->with('success', 'RT berhasil ditambahkan.');
    }

    public function update(Request $request, Rt $rt)
    {
        $this->ensureAdminRw();
        $data = $request->validate([
            'rw_id' => 'required|exists:rws,id',
            'kode_rt' => 'required|string|max:10|unique:rts,kode_rt,'.$rt->id.',id,rw_id,'.$request->input('rw_id'),
            'nama_rt' => 'nullable|string|max:255',
        ]);
        $rt->update($data);

        return back()->with('success', 'RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt)
    {
        $this->ensureAdminRw();
        abort_if($rt->wargaProfiles()->exists(), 422, 'RT yang masih memiliki warga tidak dapat dihapus.');
        $rt->delete();

        return back()->with('success', 'RT berhasil dihapus.');
    }

    private function ensureAdminRw(): void
    {
        abort_unless(Auth::user()->isAdminRw(), 403);
    }
}
