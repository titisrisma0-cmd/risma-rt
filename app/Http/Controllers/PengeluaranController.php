<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Kas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluaran = Pengeluaran::with('creator')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        return view('pengeluaran.index', compact('pengeluaran'));
    }
    
    public function create()
    {
        return view('pengeluaran.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required',
        ]);
        
        DB::beginTransaction();
        
        try {
            $pengeluaran = Pengeluaran::create([
                'tanggal' => $request->tanggal,
                'kategori' => $request->kategori,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
                'bukti' => $request->bukti,
                'created_by' => Auth::id(),
            ]);
            
            Kas::create([
                'tanggal' => $request->tanggal,
                'jenis' => 'keluar',
                'nominal' => $request->nominal,
                'sumber' => $request->kategori,
                'keterangan' => $request->keterangan,
            ]);
            
            DB::commit();
            
            return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function edit($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        return view('pengeluaran.edit', compact('pengeluaran'));
    }
    
    public function update(Request $request, $id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required',
        ]);
        
        DB::beginTransaction();
        
        try {
            $pengeluaran->update([
                'tanggal' => $request->tanggal,
                'kategori' => $request->kategori,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ]);
            
            // Update kas record
            $kas = Kas::where('tanggal', $pengeluaran->getOriginal('tanggal'))
                ->where('nominal', $pengeluaran->getOriginal('nominal'))
                ->where('keterangan', $pengeluaran->getOriginal('keterangan'))
                ->where('jenis', 'keluar')
                ->first();
            
            if ($kas) {
                $kas->update([
                    'tanggal' => $request->tanggal,
                    'nominal' => $request->nominal,
                    'sumber' => $request->kategori,
                    'keterangan' => $request->keterangan,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diupdate');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Delete related kas record
            Kas::where('tanggal', $pengeluaran->tanggal)
                ->where('nominal', $pengeluaran->nominal)
                ->where('keterangan', $pengeluaran->keterangan)
                ->where('jenis', 'keluar')
                ->delete();
            
            $pengeluaran->delete();
            
            DB::commit();
            
            return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dihapus');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }
    }
}