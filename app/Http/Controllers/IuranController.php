<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iuran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IuranController extends Controller
{
    /**
     * Pastikan hanya Pengurus RW, Bendahara, Admin RW, atau Super Admin yang dapat mengelola iuran.
     */
    private function authorizeRw(): void
    {
        $user = Auth::user();
        abort_unless(
            $user && ($user->isAdminRw() || $user->hasRole('bendahara') || $user->hasRole('admin') || $user->isSuperAdmin()),
            403,
            'Hanya pengurus RW atau Bendahara yang berhak mengelola master iuran.'
        );
    }

    public function index()
    {
        // Ambil semua data iuran
        $iuran = Iuran::orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) {
                return implode('|', [
                    strtolower(trim($item->nama_iuran)),
                    $item->jenis_iuran,
                    $item->nominal,
                    $item->periode,
                    $item->kategori,
                ]);
            });

        // Kelompokkan berdasarkan kategori
        $iuranRamadhan = $iuran->where('kategori', 'Ramadhan');
        $iuranNonRamadhan = $iuran->where('kategori', '!=', 'Ramadhan');

        // Kirim ke view index.blade.php
        return view('iuran.index', compact('iuranRamadhan', 'iuranNonRamadhan'));
    }
    
    public function create()
    {
        $this->authorizeRw();
        return view('iuran.create');
    }
    
    public function store(Request $request)
    {
        $this->authorizeRw();

        $request->merge([
            'nama_iuran' => trim($request->nama_iuran),
            'kategori'   => $request->filled('kategori') ? $request->kategori : 'Non-Ramadhan',
        ]);

        $request->validate([
            'nama_iuran' => [
                'required',
                Rule::unique('iurans', 'nama_iuran')->where(function ($query) use ($request) {
                    return $query->where('jenis_iuran', $request->jenis_iuran)
                        ->where('nominal', $request->nominal)
                        ->where('periode', $request->periode)
                        ->where('kategori', $request->kategori);
                }),
            ],
            'jenis_iuran' => 'required|in:wajib,sukarela,khusus',
            'nominal' => 'required|numeric|min:0',
            'periode' => 'required|in:bulanan,triwulan,tahunan',
            'kategori' => 'nullable|in:Ramadhan,Non-Ramadhan',
            'status' => 'nullable|in:aktif,nonaktif',
        ], [
            'nama_iuran.unique' => 'Jenis iuran dengan nama, nominal, periode, dan kategori yang sama sudah ada.',
        ]);
        
        $lastIuran = Iuran::orderBy('id', 'desc')->first();
        $kodeIuran = 'IUR' . str_pad(($lastIuran ? $lastIuran->id + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        $iuran = Iuran::create([
            'kode_iuran'   => $kodeIuran,
            'nama_iuran'   => trim($request->nama_iuran),
            'jenis_iuran'  => $request->jenis_iuran,
            'nominal'      => $request->nominal,
            'periode'      => $request->periode,
            'keterangan'   => $request->keterangan,
            'kategori'     => $request->kategori ?? 'Non-Ramadhan',
            'status'       => $request->status ?? 'aktif'
        ]);

        $msg = 'Iuran baru "' . $iuran->nama_iuran . '" berhasil ditambahkan.';

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $msg);
        }

        if ($request->headers->get('referer')) {
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->route('iuran.index')->with('success', $msg);
    }
    
    public function edit($id)
    {
        $this->authorizeRw();
        $iuran = Iuran::findOrFail($id);
        return view('iuran.edit', compact('iuran'));
    }
    
    public function update(Request $request, $id)
    {
        $this->authorizeRw();
        $iuran = Iuran::findOrFail($id);

        $request->merge([
            'nama_iuran' => trim($request->nama_iuran),
            'kategori'   => $request->filled('kategori') ? $request->kategori : ($iuran->kategori ?: 'Non-Ramadhan'),
        ]);
        
        $request->validate([
            'nama_iuran' => [
                'required',
                Rule::unique('iurans', 'nama_iuran')->ignore($iuran->id)->where(function ($query) use ($request) {
                    return $query->where('jenis_iuran', $request->jenis_iuran)
                        ->where('nominal', $request->nominal)
                        ->where('periode', $request->periode)
                        ->where('kategori', $request->kategori);
                }),
            ],
            'jenis_iuran' => 'required|in:wajib,sukarela,khusus',
            'nominal' => 'required|numeric|min:0',
            'periode' => 'required|in:bulanan,triwulan,tahunan',
            'kategori' => 'nullable|in:Ramadhan,Non-Ramadhan',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'nama_iuran.unique' => 'Jenis iuran dengan nama, nominal, periode, dan kategori yang sama sudah ada.',
        ]);
        
        $iuran->update([
            'nama_iuran' => trim($request->nama_iuran),
            'jenis_iuran' => $request->jenis_iuran,
            'nominal' => $request->nominal,
            'periode' => $request->periode,
            'kategori' => $request->kategori ?? 'Non-Ramadhan',
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);
        
        $msg = 'Iuran "' . $iuran->nama_iuran . '" berhasil diperbarui.';

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $msg);
        }

        return redirect()->route('iuran.index')->with('success', $msg);
    }
    
    public function destroy($id)
    {
        $this->authorizeRw();
        $iuran = Iuran::findOrFail($id);
        
        // Pertahankan integritas transaksi: Jika sudah ada riwayat pembayaran, nonaktifkan bukan hard-delete
        if ($iuran->pembayaran()->exists()) {
            $iuran->update(['status' => 'nonaktif']);
            return redirect()->back()->with('success', 'Iuran "' . $iuran->nama_iuran . '" memiliki data riwayat pembayaran warga, sehingga statusnya dinonaktifkan untuk menjaga arsip transaksi.');
        }

        $nama = $iuran->nama_iuran;
        $iuran->delete();
        
        return redirect()->back()->with('success', 'Iuran "' . $nama . '" berhasil dihapus.');
    }
    
    public function toggleStatus($id)
    {
        $this->authorizeRw();
        $iuran = Iuran::findOrFail($id);
        $iuran->status = $iuran->status == 'aktif' ? 'nonaktif' : 'aktif';
        $iuran->save();
        
        return redirect()->back()->with('success', 'Status iuran "' . $iuran->nama_iuran . '" berhasil diubah menjadi ' . $iuran->status . '.');
    }
}
