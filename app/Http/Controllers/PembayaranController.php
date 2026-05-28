<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Iuran;
use App\Models\Kas;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        $currentPeriod = date('Y-m');

        if (Auth::user()->hasRole('warga')) {
            $pembayaran = Pembayaran::with(['iuran', 'verifikator'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            $activeIuran = Iuran::where('status', 'aktif')
                ->orderBy('nama_iuran')
                ->orderBy('nominal')
                ->orderBy('periode')
                ->orderBy('kategori')
                ->get();

            $activeIuran = Iuran::dedupeCollection($activeIuran);

            $tagihan = $activeIuran->map(function ($iuran) use ($currentPeriod) {
                $iuranIds = $iuran->duplicate_ids ?? collect([$iuran->id]);

                $lastPembayaran = Pembayaran::where('user_id', Auth::id())
                    ->whereIn('iuran_id', $iuranIds)
                    ->where('bulan_tahun', $currentPeriod)
                    ->orderBy('created_at', 'desc')
                    ->first();

                return (object) [
                    'iuran' => $iuran,
                    'bulan_tahun' => $currentPeriod,
                    'status' => $lastPembayaran ? $lastPembayaran->status : 'unpaid',
                    'pembayaran' => $lastPembayaran,
                ];
            });
        } else {
            $pembayaran = Pembayaran::with(['user', 'iuran', 'verifikator'])
                ->orderBy('created_at', 'desc')
                ->get();
            $tagihan = collect();
        }
        
        return view('pembayaran.index', compact('pembayaran', 'tagihan'));
    }
    
    public function create(Request $request)
    {
        $warga = User::whereHas('role', function($q) {
            $q->where('nama_role', 'warga');
        })->get();
        
        $iuran = Iuran::where('status', 'aktif')
            ->orderBy('nama_iuran')
            ->orderBy('nominal')
            ->orderBy('periode')
            ->orderBy('id')
            ->get();

        $iuran = Iuran::dedupeCollection($iuran);

        $selectedIuranId = $request->query('iuran_id');
        $selectedBulanTahun = $request->query('bulan_tahun', date('Y-m'));
        $selectedIuran = null;
        $dendaAmount = 0;
        $dueDate = null;

        if ($selectedIuranId) {
            $selectedIuran = Iuran::find($selectedIuranId);
            if ($selectedIuran) {
                try {
                    [$y, $m] = explode('-', $selectedBulanTahun);
                    $dendaAmount = 0;
                    $dueDay = $selectedIuran->due_day ?? 10;
                    $dueDate = Carbon::createFromDate((int)$y, (int)$m, (int)$dueDay)->endOfDay();
                    if (Carbon::now()->gt($dueDate)) {
                        $dendaPercent = $selectedIuran->denda_persen ?? 10.0;
                        $dendaAmount = round(($selectedIuran->nominal * ($dendaPercent / 100)), 2);
                    }
                } catch (\Exception $e) {
                    $dendaAmount = 0;
                }
            }
        }

        return view('pembayaran.create', compact('warga', 'iuran', 'selectedIuranId', 'selectedBulanTahun', 'selectedIuran', 'dendaAmount', 'dueDate'));
    }
    
    public function store(Request $request)
    {
        $isWarga = Auth::user()->hasRole('warga');

        // Validasi
        $rules = [
            'user_id' => 'required',
            'iuran_id' => 'required',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:tunai,transfer,ewallet,qris',
            'bulan_tahun' => 'nullable',
            'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($isWarga) {
            $rules['user_id'] = 'required|in:' . Auth::id();
            $rules['bukti_transfer'] = 'required_if:metode_bayar,transfer,ewallet,qris|image|mimes:jpeg,png,jpg|max:2048';
        }
        
        if ($request->metode_bayar == 'ewallet') {
            $rules['ewallet_provider'] = 'required|string';
        }
        
        $request->validate($rules);
        
        DB::beginTransaction();
        
        try {
            $bulanTahun = $request->bulan_tahun ?: date('Y-m');

            if ($isWarga) {
                $existingPayment = Pembayaran::where('user_id', Auth::id())
                    ->where('iuran_id', $request->iuran_id)
                    ->where('bulan_tahun', $bulanTahun)
                    ->whereIn('status', ['pending', 'lunas'])
                    ->first();

                if ($existingPayment) {
                    DB::rollBack();

                    $message = $existingPayment->status === 'pending'
                        ? 'Pembayaran untuk iuran dan periode ini sudah dikirim dan masih menunggu verifikasi admin/bendahara.'
                        : 'Iuran periode ini sudah lunas.';

                    return redirect()->route('pembayaran.index')->with('error', $message);
                }
            }

            $lastTransaksi = Pembayaran::orderBy('id', 'desc')->first();
            $noTransaksi = 'TRX' . date('Ymd') . str_pad(($lastTransaksi ? $lastTransaksi->id + 1 : 1), 4, '0', STR_PAD_LEFT);
            $paymentStatus = $isWarga ? 'pending' : 'lunas';
            
            // Initialize data array dengan urutan yang sesuai fillable model
            $data = [
                'no_transaksi' => $noTransaksi,
                'user_id' => $request->user_id,
                'iuran_id' => $request->iuran_id,
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $request->jumlah_bayar,
                'denda' => 0,
                'metode_bayar' => $request->metode_bayar,
                'bukti_transfer' => null,
                'status' => $paymentStatus,
                'bulan_tahun' => $bulanTahun,
                'keterangan' => $request->keterangan ?? null,
                'verified_by' => $isWarga ? null : Auth::id(),
                'verified_at' => $isWarga ? null : now(),
            ];

            // Tambah metode pembayaran ke keterangan jika e-wallet
            if ($request->metode_bayar == 'ewallet') {
                $data['keterangan'] = ($data['keterangan'] ? $data['keterangan'] . ' | ' : '') . 'E-Wallet: ' . $request->ewallet_provider;
            }

            // Hitung denda jika telat berdasarkan konfigurasi iuran
            $iuran = Iuran::find($request->iuran_id);
            if ($iuran) {
                try {
                    [$y, $m] = explode('-', $bulanTahun);
                    $dueDay = $iuran->due_day ?? 10;
                    $dueDate = Carbon::createFromDate((int)$y, (int)$m, (int)$dueDay)->endOfDay();
                    if (Carbon::now()->gt($dueDate)) {
                        $dendaPercent = $iuran->denda_persen ?? 10.0;
                        $dendaAmount = round(($iuran->nominal * ($dendaPercent / 100)), 2);
                        $data['denda'] = $dendaAmount;
                        $data['jumlah_bayar'] = $request->jumlah_bayar + $dendaAmount;
                        $data['keterangan'] = ($data['keterangan'] ? $data['keterangan'] . ' | ' : '') . "Denda telat bayar: Rp " . number_format($dendaAmount, 0, ',', '.');

                        if (!$isWarga) {
                            // Buat notifikasi kepada warga saat pembayaran dicatat petugas.
                            Notifikasi::create([
                                'user_id' => $request->user_id,
                                'from_user_id' => Auth::id(),
                                'iuran_id' => $iuran->id,
                                'tipe' => 'iuran',
                                'judul' => 'Denda Iuran',
                                'pesan' => "Anda dikenakan denda sebesar Rp " . number_format($dendaAmount, 0, ',', '.') . " untuk iuran {$iuran->nama_iuran} periode {$bulanTahun} karena keterlambatan.",
                                'dibaca' => false,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // jika format bulan_tahun salah, abaikan denda
                }
            }
            
            // Upload bukti transfer jika ada
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('bukti-transfer', $filename, 'public');
                $data['bukti_transfer'] = $path;
            }
            
            $pembayaran = Pembayaran::create($data);

            if ($isWarga) {
                $petugas = User::whereHas('role', function ($q) {
                    $q->whereIn('nama_role', ['admin', 'bendahara']);
                })->get();

                foreach ($petugas as $userPetugas) {
                    Notifikasi::create([
                        'user_id' => $userPetugas->id,
                        'from_user_id' => Auth::id(),
                        'iuran_id' => $request->iuran_id,
                        'tipe' => 'pembayaran',
                        'judul' => 'Pembayaran Perlu Verifikasi',
                        'pesan' => Auth::user()->name . ' mengirim pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . '. Silakan cek bukti pembayaran sebelum verifikasi.',
                        'dibaca' => false,
                    ]);
                }
            }
            
            if ($data['status'] === 'lunas') {
                Kas::create([
                    'tanggal' => now(),
                    'jenis' => 'masuk',
                    'nominal' => $data['jumlah_bayar'],
                    'sumber' => 'Iuran',
                    'keterangan' => "Pembayaran iuran dari " . User::find($request->user_id)->name,
                    'pembayaran_id' => $pembayaran->id,
                ]);
            }

            // Notifikasi status pembayaran untuk warga
            if ($request->user_id) {
                $iuran = Iuran::find($request->iuran_id);
                Notifikasi::create([
                    'user_id' => $request->user_id,
                    'from_user_id' => Auth::id(),
                    'iuran_id' => $request->iuran_id,
                    'tipe' => 'pembayaran',
                    'judul' => $isWarga ? 'Pembayaran Menunggu Verifikasi' : 'Pembayaran Berhasil',
                    'pesan' => $isWarga
                        ? 'Pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . ' berhasil dikirim dan menunggu verifikasi admin/bendahara.'
                        : 'Pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . ' berhasil diverifikasi. Terima kasih.',
                    'dibaca' => false,
                ]);
            }
            
            DB::commit();
            
            $message = $isWarga
                ? 'Pembayaran berhasil dikirim. Silakan tunggu verifikasi admin/bendahara.'
                : 'Pembayaran berhasil diproses dan status telah diubah menjadi Lunas';

            return redirect()->route('pembayaran.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    public function verifikasi($id)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('bendahara')) {
            abort(403, 'Hanya admin atau bendahara yang dapat memverifikasi pembayaran.');
        }

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
