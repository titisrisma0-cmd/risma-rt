<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iuran;
use Illuminate\Validation\Rule;

class IuranController extends Controller
{
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
        $iuranNonRamadhan = $iuran->where('kategori', 'Non-Ramadhan');

        // Kirim ke view index.blade.php
        return view('iuran.index', compact('iuranRamadhan', 'iuranNonRamadhan'));
    }
    
    public function create()
    {
        return view('iuran.create');
    }
    
    public function store(Request $request)
    {
        $request->merge([
            'nama_iuran' => trim($request->nama_iuran),
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
            'nominal' => 'required|numeric',
            'periode' => 'required|in:bulanan,triwulan,tahunan',
            'kategori' => 'required|in:Ramadhan,Non-Ramadhan',
            'status' => 'nullable|in:aktif,nonaktif',
        ], [
            'nama_iuran.unique' => 'Jenis iuran dengan nama, nominal, periode, dan kategori yang sama sudah ada.',
        ]);
        
        $lastIuran = Iuran::orderBy('id', 'desc')->first();
        $kodeIuran = 'IUR' . str_pad(($lastIuran ? $lastIuran->id + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        Iuran::create([
            'kode_iuran'   => $kodeIuran,
            'nama_iuran'   => trim($request->nama_iuran),
            'jenis_iuran'  => $request->jenis_iuran,
            'nominal'      => $request->nominal,
            'periode'      => $request->periode,
            'keterangan'   => $request->keterangan,
            'kategori'     => $request->kategori,
            'status'       => $request->status ?? 'aktif'
        ]);

        
        return redirect()->route('iuran.index')->with('success', 'Iuran berhasil ditambahkan');
    }
    
    public function edit($id)
    {
        $iuran = Iuran::findOrFail($id);
        return view('iuran.edit', compact('iuran'));
    }
    
    public function update(Request $request, $id)
    {
        $iuran = Iuran::findOrFail($id);

        $request->merge([
            'nama_iuran' => trim($request->nama_iuran),
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
            'nominal' => 'required|numeric',
            'periode' => 'required|in:bulanan,triwulan,tahunan',
            'kategori' => 'required|in:Ramadhan,Non-Ramadhan',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'nama_iuran.unique' => 'Jenis iuran dengan nama, nominal, periode, dan kategori yang sama sudah ada.',
        ]);
        
        $iuran->update([
            'nama_iuran' => trim($request->nama_iuran),
            'jenis_iuran' => $request->jenis_iuran,
            'nominal' => $request->nominal,
            'periode' => $request->periode,
            'kategori' => $request->kategori,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);
        
        return redirect()->route('iuran.index')->with('success', 'Iuran berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $iuran = Iuran::findOrFail($id);
        $iuran->delete();
        
        return redirect()->route('iuran.index')->with('success', 'Iuran berhasil dihapus');
    }
    
    public function toggleStatus($id)
    {
        $iuran = Iuran::findOrFail($id);
        $iuran->status = $iuran->status == 'aktif' ? 'nonaktif' : 'aktif';
        $iuran->save();
        
        return redirect()->route('iuran.index')->with('success', 'Status iuran berhasil diubah menjadi ' . $iuran->status);
    }
}
