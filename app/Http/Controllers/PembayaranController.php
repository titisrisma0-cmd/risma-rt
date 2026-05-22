<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Iuran;
use App\Models\Kas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasRole('warga')) {
            $pembayaran = Pembayaran::with(['iuran', 'verifikator'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $pembayaran = Pembayaran::with(['user', 'iuran', 'verifikator'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('pembayaran.index', compact('pembayaran'));
    }
    
    public function create()
    {
        $warga = User::whereHas('role', function($q) {
            $q->where('nama_role', 'warga');
        })->get();
        
        $iuran = Iuran::where('status', 'aktif')->get();
        
        return view('pembayaran.create', compact('warga', 'iuran'));
    }
    
    public function store(Request $request)
    {
        // Validasi
        $rules = [
            'user_id' => 'required',
            'iuran_id' => 'required',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required',
            'bulan_tahun' => 'nullable',
        ];
        
        // Jika metode transfer, bukti transfer wajib
        if ($request->metode_bayar == 'transfer') {
            $rules['bukti_transfer'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        }
        
        $request->validate($rules);
        
        DB::beginTransaction();
        
        try {
            $lastTransaksi = Pembayaran::orderBy('id', 'desc')->first();
            $noTransaksi = 'TRX' . date('Ymd') . str_pad(($lastTransaksi ? $lastTransaksi->id + 1 : 1), 4, '0', STR_PAD_LEFT);
            
            $data = [
                'no_transaksi' => $noTransaksi,
                'user_id' => $request->user_id,
                'iuran_id' => $request->iuran_id,
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_bayar' => $request->metode_bayar,
                'status' => Auth::user()->hasRole('bendahara') ? 'lunas' : 'pending',
                'bulan_tahun' => $request->bulan_tahun,
                'keterangan' => $request->keterangan,
                'verified_by' => Auth::user()->hasRole('bendahara') ? Auth::id() : null,
                'verified_at' => Auth::user()->hasRole('bendahara') ? now() : null,
            ];
            
            // Upload bukti transfer
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('bukti-transfer', $filename, 'public');
                $data['bukti_transfer'] = $path;
            }
            
            $pembayaran = Pembayaran::create($data);
            
            // Jika bendahara yang input, langsung catat ke kas
            if (Auth::user()->hasRole('bendahara')) {
                Kas::create([
                    'tanggal' => now(),
                    'jenis' => 'masuk',
                    'nominal' => $request->jumlah_bayar,
                    'sumber' => 'Iuran',
                    'keterangan' => "Pembayaran iuran dari " . User::find($request->user_id)->name,
                    'pembayaran_id' => $pembayaran->id,
                ]);
            }
            
            DB::commit();
            
            $message = Auth::user()->hasRole('bendahara') 
                ? 'Pembayaran berhasil dicatat' 
                : 'Pembayaran berhasil diajukan, menunggu verifikasi bendahara';
            
            return redirect()->route('pembayaran.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    public function verifikasi($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            $pembayaran->update([
                'status' => 'lunas',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
            
            Kas::create([
                'tanggal' => now(),
                'jenis' => 'masuk',
                'nominal' => $pembayaran->jumlah_bayar,
                'sumber' => 'Iuran',
                'keterangan' => "Verifikasi pembayaran iuran dari " . $pembayaran->user->name,
                'pembayaran_id' => $pembayaran->id,
            ]);
            
            DB::commit();
            
            return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diverifikasi');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }
    }
    
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        
        // Hapus file bukti transfer
        if ($pembayaran->bukti_transfer && Storage::disk('public')->exists($pembayaran->bukti_transfer)) {
            Storage::disk('public')->delete($pembayaran->bukti_transfer);
        }
        
        if ($pembayaran->kas) {
            $pembayaran->kas->delete();
        }
        
        $pembayaran->delete();
        
        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dihapus');
    }
    
    public function getTotalBayar($iuranId)
    {
        $iuran = Iuran::find($iuranId);
        return response()->json(['nominal' => $iuran ? $iuran->nominal : 0]);
    }
}