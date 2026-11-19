<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RfidDevice;
use App\Models\Rt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RfidDeviceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = RfidDevice::with('rt');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhere('nama_lokasi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fungsi')) {
            $query->where('fungsi', $request->input('fungsi'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $devices = $query->orderBy('nama_lokasi')->paginate(15)->withQueryString();

        $stats = [
            'total' => RfidDevice::count(),
            'gerbang' => RfidDevice::where('fungsi', RfidDevice::FUNGSI_GERBANG)->count(),
            'bank_sampah' => RfidDevice::where('fungsi', RfidDevice::FUNGSI_BANK_SAMPAH)->count(),
            'presensi' => RfidDevice::where('fungsi', RfidDevice::FUNGSI_PRESENSI)->count(),
            'perekaman' => RfidDevice::where('fungsi', RfidDevice::FUNGSI_PEREKAMAN)->count(),
            'unconfigured' => RfidDevice::where('fungsi', RfidDevice::FUNGSI_BELUM_DIKONFIGURASI)->count(),
            'active' => RfidDevice::where('is_active', true)->count(),
        ];

        $rts = Rt::orderBy('kode_rt')->get();

        return view('superadmin.rfid-devices.index', compact('devices', 'stats', 'rts'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100', 'unique:rfid_devices,device_id'],
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'fungsi' => ['required', Rule::in(RfidDevice::VALID_FUNCTIONS)],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        $device = RfidDevice::create($validated);

        AuditLog::record(
            action: 'create_rfid_device',
            entityType: 'RfidDevice',
            entityId: $device->id,
            deskripsi: "Super Admin mendaftarkan perangkat RFID baru: {$device->device_id} ({$device->nama_lokasi}) fungsi {$device->fungsi}"
        );

        return redirect()->route('superadmin.rfid-devices.index')
            ->with('success', "Perangkat RFID '{$device->device_id}' berhasil ditambahkan.");
    }

    public function update(Request $request, RfidDevice $device)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100', Rule::unique('rfid_devices', 'device_id')->ignore($device->id)],
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'fungsi' => ['required', Rule::in(RfidDevice::VALID_FUNCTIONS)],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldFungsi = $device->fungsi;
        $device->update($validated);

        AuditLog::record(
            action: 'update_rfid_device',
            entityType: 'RfidDevice',
            entityId: $device->id,
            deskripsi: "Super Admin memperbarui perangkat RFID {$device->device_id}: lokasi {$device->nama_lokasi}, fungsi {$oldFungsi} -> {$device->fungsi}"
        );

        return redirect()->route('superadmin.rfid-devices.index')
            ->with('success', "Konfigurasi perangkat '{$device->device_id}' berhasil diperbarui.");
    }

    public function toggle(RfidDevice $device)
    {
        $this->authorizeSuperAdmin();

        $device->is_active = ! $device->is_active;
        $device->save();

        $statusStr = $device->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record(
            action: 'toggle_rfid_device',
            entityType: 'RfidDevice',
            entityId: $device->id,
            deskripsi: "Super Admin mengubah status perangkat RFID {$device->device_id} menjadi {$statusStr}"
        );

        return back()->with('success', "Perangkat RFID '{$device->device_id}' berhasil {$statusStr}.");
    }

    public function destroy(RfidDevice $device)
    {
        $this->authorizeSuperAdmin();

        $deviceId = $device->device_id;
        $device->delete();

        AuditLog::record(
            action: 'delete_rfid_device',
            entityType: 'RfidDevice',
            entityId: null,
            deskripsi: "Super Admin menghapus perangkat RFID {$deviceId}"
        );

        return redirect()->route('superadmin.rfid-devices.index')
            ->with('success', "Perangkat RFID '{$deviceId}' berhasil dihapus.");
    }

    private function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        $isRealSuperAdmin = $user && $user->isSuperAdmin() && ! $user->isSimulatingRole();
        abort_unless($isRealSuperAdmin, 403, 'Akses khusus Super Admin.');
    }
}
