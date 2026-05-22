<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iuran;

class IuranController extends Controller
{
    public function index()
    {
        $iuran = Iuran::orderBy('created_at', 'desc')->get();
        return view('iuran.index', compact('iuran'));
    }
    
    public function create()
    {
        return view('iuran.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_iuran' => 'required',
            'jenis_iuran' => 'required',
            'nominal' => 'required|numeric',
            'periode' => 'required',
        ]);
        
        $lastIuran = Iuran::orderBy('id', 'desc')->first();
        $kodeIuran = 'IUR' . str_pad(($lastIuran ? $lastIuran->id + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        Iuran::create([
            'kode_iuran' => $kodeIuran,
            'nama_iuran' => $request->nama_iuran,
            'jenis_iuran' => $request->jenis_iuran,
            'nominal' => $request->nominal,
            'periode' => $request->periode,
            'keterangan' => $request->keterangan,
            'status' => 'aktif'
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
        
        $request->validate([
            'nama_iuran' => 'required',
            'jenis_iuran' => 'required',
            'nominal' => 'required|numeric',
            'periode' => 'required',
        ]);
        
        $iuran->update($request->all());
        
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