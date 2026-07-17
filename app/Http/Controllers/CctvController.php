<?php

namespace App\Http\Controllers;

use App\Models\CctvCamera;
use App\Models\CctvRecording;
use App\Models\Rt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CctvController extends Controller
{
    public function live(Request $request)
    {
        $this->authorize('viewAny', CctvCamera::class);
        $query = CctvCamera::with('rt')->orderBy('rt_id')->orderBy('nama_lokasi');
        if ($request->filled('rt_id') && Auth::user()->isAdminRw()) $query->where('rt_id', $request->integer('rt_id'));
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('q')) $query->where('nama_lokasi', 'like', '%'.$request->q.'%');

        return view('layanan.cctv.live', [
            'cameraGroups' => $query->get()->groupBy('rt_id'),
            'rts' => $this->availableRts(),
        ]);
    }

    public function recordings(Request $request)
    {
        $this->authorize('viewAny', CctvRecording::class);
        $query = CctvRecording::with('camera.rt')->latest('tanggal_rekaman')->latest('jam_mulai');
        if ($request->filled('tanggal_mulai')) $query->whereDate('tanggal_rekaman', '>=', $request->tanggal_mulai);
        if ($request->filled('tanggal_selesai')) $query->whereDate('tanggal_rekaman', '<=', $request->tanggal_selesai);
        if ($request->filled('camera_id')) $query->where('camera_id', $request->integer('camera_id'));
        if ($request->filled('rt_id') && Auth::user()->isAdminRw()) {
            $query->whereHas('camera', fn ($q) => $q->where('rt_id', $request->integer('rt_id')));
        }

        return view('layanan.cctv.recordings', [
            'recordings' => $query->paginate(20)->withQueryString(),
            'cameras' => CctvCamera::with('rt')->orderBy('rt_id')->orderBy('nama_lokasi')->get(),
            'rts' => $this->availableRts(),
        ]);
    }

    public function manage()
    {
        $this->authorize('viewAny', CctvCamera::class);
        return view('layanan.cctv.manage', [
            'cameras' => CctvCamera::with('rt')->withCount('recordings')->orderBy('rt_id')->orderBy('nama_lokasi')->get(),
            'rts' => $this->availableRts(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CctvCamera::class);
        CctvCamera::create($this->validatedCamera($request));
        return back()->with('success', 'Kamera CCTV berhasil ditambahkan.');
    }

    public function update(Request $request, int $camera)
    {
        $camera = $this->cameraForAuthorization($camera);
        $this->authorize('update', $camera);
        $camera->update($this->validatedCamera($request));
        return back()->with('success', 'Kamera CCTV berhasil diperbarui.');
    }

    public function destroy(int $camera)
    {
        $camera = $this->cameraForAuthorization($camera);
        $this->authorize('delete', $camera);
        $camera->delete();
        return back()->with('success', 'Kamera dan arsip demonya berhasil dihapus.');
    }

    public function toggleStatus(int $camera)
    {
        $camera = $this->cameraForAuthorization($camera);
        $this->authorize('update', $camera);
        $camera->update(['status' => $camera->status === 'aktif' ? 'nonaktif' : 'aktif']);
        return back()->with('success', 'Status kamera berhasil diubah.');
    }

    private function validatedCamera(Request $request): array
    {
        $data = $request->validate([
            'rt_id' => Auth::user()->isAdminRw() ? 'required|exists:rts,id' : 'nullable',
            'nama_lokasi' => 'required|string|max:255',
            'stream_url' => 'nullable|url|max:2048',
            'status' => 'required|in:aktif,nonaktif,rusak',
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);
        $data['rt_id'] = Auth::user()->isAdminRt() ? Auth::user()->managedRtId() : (int) $data['rt_id'];
        abort_if(! $data['rt_id'], 403, 'Admin RT belum ditugaskan ke RT.');
        return $data;
    }

    private function cameraForAuthorization(int $id): CctvCamera
    {
        return CctvCamera::withoutGlobalScopes()->with('rt')->findOrFail($id);
    }

    private function availableRts()
    {
        return Auth::user()->isAdminRt()
            ? Rt::whereKey(Auth::user()->managedRtId())->get()
            : Rt::with('rw')->orderBy('rw_id')->orderBy('kode_rt')->get();
    }
}
