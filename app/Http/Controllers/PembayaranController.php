<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Iuran;
use App\Models\Kas;
use App\Models\Notifikasi;
use App\Models\MetodePembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        $currentPeriod = Carbon::now()->format('Y-m');
        $previousPeriod = Carbon::now()->subMonthNoOverflow()->format('Y-m');
        $user = Auth::user();

        if ($user->hasRole('warga')) {
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

            $periods = collect([
                [
                    'value' => $currentPeriod,
                    'label' => 'Bulan Ini',
                    'month_label' => Carbon::parse($currentPeriod . '-01')->locale('id')->translatedFormat('F Y'),
                    'badge_class' => 'bg-primary',
                ],
                [
                    'value' => $previousPeriod,
                    'label' => 'Bulan Sebelumnya',
                    'month_label' => Carbon::parse($previousPeriod . '-01')->locale('id')->translatedFormat('F Y'),
                    'badge_class' => 'bg-info',
                ],
            ]);

            $tagihanGroups = $periods->map(function ($period) use ($activeIuran) {
                $items = $activeIuran->map(function ($iuran) use ($period) {
                    $iuranIds = $iuran->duplicate_ids ?? collect([$iuran->id]);

                    $lastPembayaran = Pembayaran::where('user_id', Auth::id())
                        ->whereIn('iuran_id', $iuranIds)
                        ->where('bulan_tahun', $period['value'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return (object) [
                        'iuran' => $iuran,
                        'bulan_tahun' => $period['value'],
                        'periode_label' => $period['label'],
                        'status' => $lastPembayaran ? $lastPembayaran->status : 'unpaid',
                        'pembayaran' => $lastPembayaran,
                    ];
                });

                return (object) [
                    'period' => (object) $period,
                    'items' => $items,
                    'total' => $items->count(),
                    'unpaid_count' => $items->where('status', 'unpaid')->count(),
                    'pending_count' => $items->where('status', 'pending')->count(),
                    'paid_count' => $items->where('status', 'lunas')->count(),
                ];
            });

            $tagihan = $tagihanGroups->flatMap->items;
        } elseif ($user->isAdminRt()) {
            // Khusus Ketua RT: Hanya menampilkan pembayaran warga di wilayah RT-nya
            $rtId = $user->managedRtId();
            $pembayaran = Pembayaran::with(['user.profile', 'iuran', 'verifikator'])
                ->whereHas('user.profile', function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            $tagihan = collect();
            $tagihanGroups = collect();
        } else {
            // RW / Admin / Bendahara: Akses seluruh pembayaran
            $pembayaran = Pembayaran::with(['user.profile', 'iuran', 'verifikator'])
                ->orderBy('created_at', 'desc')
                ->get();
            $tagihan = collect();
            $tagihanGroups = collect();
        }
        
        return view('pembayaran.index', compact('pembayaran', 'tagihan', 'tagihanGroups'));
    }
    
    public function create(Request $request)
    {
        $user = Auth::user();
        $wargaQuery = User::whereHas('role', function ($q) {
            $q->where('nama_role', 'warga');
        })->whereHas('profile', function ($q) {
            $q->where('status', 'aktif');
        })->with('profile');

        // Pembatasan pemilihan warga jika user adalah Ketua RT
        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $wargaQuery->whereHas('profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
        }

        $warga = $wargaQuery->orderBy('name')->get();

        $activeMetodes = MetodePembayaran::active()->get();
        $bankMetodes = $activeMetodes->where('kategori', 'bank');
        $ewalletMetodes = $activeMetodes->where('kategori', 'ewallet');
        $qrisMetode = $activeMetodes->firstWhere('kategori', 'qris');
        
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

        return view('pembayaran.create', compact(
            'warga', 'iuran', 'selectedIuranId', 'selectedBulanTahun', 'selectedIuran', 'dendaAmount', 'dueDate',
            'activeMetodes', 'bankMetodes', 'ewalletMetodes', 'qrisMetode'
        ));
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        $isWarga = $user->hasRole('warga');

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

        // Keamanan Server-Side RT: Pastikan warga yang dibayar berada di wilayah RT bersangkutan
        if ($user->isAdminRt()) {
            $targetUser = User::with('profile')->findOrFail($request->user_id);
            $rtId = $user->managedRtId();
            abort_if(
                (int) optional($targetUser->profile)->rt_id !== (int) $rtId,
                403,
                'Akses ditolak - Warga tidak berada di wilayah RT Anda.'
            );
        }
        
        DB::beginTransaction();
        
        try {
            $bulanTahun = $request->bulan_tahun ?: date('Y-m');

            // Cek duplikasi pembayaran (mencegah pembayaran ganda baik oleh Warga maupun input pengurus RT/RW)
            $existingPayment = Pembayaran::where('user_id', $request->user_id)
                ->where('iuran_id', $request->iuran_id)
                ->where('bulan_tahun', $bulanTahun)
                ->whereIn('status', ['pending', 'lunas'])
                ->first();

            if ($existingPayment) {
                DB::rollBack();

                $message = $existingPayment->status === 'pending'
                    ? 'Pembayaran untuk warga, iuran, dan periode ini sudah tercatat dan masih menunggu verifikasi.'
                    : 'Iuran warga untuk periode ini sudah berstatus lunas sebelumnya.';

                return redirect()->back()->withInput()->with('error', $message);
            }

            $lastTransaksi = Pembayaran::orderBy('id', 'desc')->first();
            $noTransaksi = 'TRX' . date('Ymd') . str_pad(($lastTransaksi ? $lastTransaksi->id + 1 : 1), 4, '0', STR_PAD_LEFT);
            $paymentStatus = $isWarga ? 'pending' : 'lunas';
            
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

            if ($request->metode_bayar == 'ewallet') {
                $data['keterangan'] = ($data['keterangan'] ? $data['keterangan'] . ' | ' : '') . 'E-Wallet: ' . $request->ewallet_provider;
            }

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
                    // Abaikan kesalahan parsing tanggal
                }
            }
            
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('bukti-transfer', $filename, 'public');
                $data['bukti_transfer'] = $path;
            }
            
            $pembayaran = Pembayaran::create($data);
            $wargaRtId = optional(User::with('profile')->find($request->user_id))->profile?->rt_id;

            // Audit Trail
            AuditLog::record(
                action: 'pembayaran_iuran',
                entityType: 'pembayaran',
                entityId: $pembayaran->id,
                deskripsi: "Pencatatan pembayaran iuran {$noTransaksi} warga {$pembayaran->user->name} sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " ({$paymentStatus})",
                nominal: $pembayaran->jumlah_bayar,
                bukti: $pembayaran->bukti_transfer,
                rtId: $wargaRtId,
                userId: Auth::id()
            );

            if ($isWarga && $wargaRtId) {
                // Notifikasi khusus Ketua RT wilayah bersangkutan
                $rtModel = \App\Models\Rt::find($wargaRtId);
                if ($rtModel && $rtModel->ketua_user_id) {
                    Notifikasi::create([
                        'user_id' => $rtModel->ketua_user_id,
                        'from_user_id' => Auth::id(),
                        'iuran_id' => $request->iuran_id,
                        'tipe' => 'pembayaran',
                        'judul' => 'Pembayaran Warga Perlu Verifikasi',
                        'pesan' => Auth::user()->name . ' (RT ' . $rtModel->kode_rt . ') mengirim pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . '. Silakan verifikasi.',
                        'dibaca' => false,
                    ]);
                }
            }
            
            if ($data['status'] === 'lunas') {
                $wargaUser = User::with('profile')->find($request->user_id);
                Kas::create([
                    'rt_id' => optional($wargaUser->profile)->rt_id,
                    'rw_id' => 1,
                    'tanggal' => now(),
                    'jenis' => 'masuk',
                    'nominal' => $data['jumlah_bayar'],
                    'sumber' => 'Iuran',
                    'keterangan' => "Pembayaran iuran dari " . $wargaUser->name,
                    'pembayaran_id' => $pembayaran->id,
                    'created_by' => Auth::id(),
                ]);
            }

            if ($request->user_id) {
                $iuran = Iuran::find($request->iuran_id);
                Notifikasi::create([
                    'user_id' => $request->user_id,
                    'from_user_id' => Auth::id(),
                    'iuran_id' => $request->iuran_id,
                    'tipe' => 'pembayaran',
                    'judul' => $isWarga ? 'Pembayaran Menunggu Verifikasi' : 'Pembayaran Berhasil',
                    'pesan' => $isWarga
                        ? 'Pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . ' berhasil dikirim dan menunggu verifikasi.'
                        : 'Pembayaran iuran ' . ($iuran ? $iuran->nama_iuran : '') . ' periode ' . $bulanTahun . ' berhasil diproses (Lunas). Terima kasih.',
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

    public function show($id)
    {
        $pembayaran = $this->authorizePembayaran($id);
        return redirect()->route('pembayaran.index');
    }
    
    public function verifikasi($id)
    {
        $user = Auth::user();
        if (! $user->hasRole('admin') && ! $user->hasRole('bendahara') && ! $user->isAdminRw() && ! $user->isAdminRt()) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi pembayaran.');
        }

        $pembayaran = $this->authorizePembayaran($id);
        
        DB::beginTransaction();
        
        try {
            $pembayaran->update([
                'status' => 'lunas',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
            
            Kas::create([
                'rt_id' => optional($pembayaran->user->profile)->rt_id,
                'rw_id' => 1,
                'tanggal' => now(),
                'jenis' => 'masuk',
                'nominal' => $pembayaran->jumlah_bayar,
                'sumber' => 'Iuran',
                'keterangan' => "Verifikasi pembayaran iuran dari " . $pembayaran->user->name,
                'pembayaran_id' => $pembayaran->id,
                'created_by' => Auth::id(),
            ]);

            // Audit Trail
            AuditLog::record(
                action: 'pembayaran_verifikasi',
                entityType: 'pembayaran',
                entityId: $pembayaran->id,
                deskripsi: "Verifikasi lunas pembayaran iuran {$pembayaran->no_transaksi} ({$pembayaran->user->name}) sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " oleh " . $user->name,
                nominal: $pembayaran->jumlah_bayar,
                bukti: $pembayaran->bukti_transfer,
                rtId: optional($pembayaran->user->profile)->rt_id,
                userId: Auth::id()
            );

            // Notify citizen
            Notifikasi::create([
                'user_id' => $pembayaran->user_id,
                'from_user_id' => Auth::id(),
                'iuran_id' => $pembayaran->iuran_id,
                'tipe' => 'pembayaran',
                'judul' => 'Pembayaran Diverifikasi (Lunas)',
                'pesan' => "Pembayaran iuran Anda ({$pembayaran->no_transaksi}) telah diverifikasi oleh pengurus dan dinyatakan LUNAS. Terima kasih.",
                'dibaca' => false,
            ]);
            
            DB::commit();
            
            return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diverifikasi');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        $pembayaran = $this->authorizePembayaran($id);
        
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

    private function authorizePembayaran($id): Pembayaran
    {
        $user = Auth::user();
        $pembayaran = Pembayaran::with('user.profile')->findOrFail($id);

        if ($user->hasRole('warga')) {
            abort_if((int) $pembayaran->user_id !== (int) $user->id, 403, 'Akses ditolak.');
            return $pembayaran;
        }

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $pembayaranRtId = optional($pembayaran->user->profile)->rt_id;
            abort_if(
                (int) $pembayaranRtId !== (int) $rtId,
                403,
                'Akses ditolak - Data transaksi berada di luar wilayah RT Anda.'
            );
        }

        return $pembayaran;
    }
}
