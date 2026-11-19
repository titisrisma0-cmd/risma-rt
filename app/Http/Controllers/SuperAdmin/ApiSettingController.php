<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiSettingController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Token API Perangkat RFID
     */
    public function index()
    {
        $tokens = DeviceToken::with('creator')->latest()->get();

        $rejectedLogs = AuditLog::with(['user', 'rt'])
            ->where('action', 'rfid_token_rejected')
            ->latest()
            ->paginate(15);

        $cardProfile = [
            'sak' => AppSetting::get('rfid_profile_sak', ''),
            'uid_size' => AppSetting::get('rfid_profile_uid_size', ''),
            'card_type' => AppSetting::get('rfid_profile_card_type', ''),
        ];

        return view('superadmin.api-settings.index', compact('tokens', 'rejectedLogs', 'cardProfile'));
    }

    /**
     * Generate token API perangkat baru
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $plainToken = Str::random(40);

        $token = DeviceToken::create([
            'name' => $validated['name'],
            'token_hash' => DeviceToken::hashToken($plainToken),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        AuditLog::record(
            action: 'generate_device_token',
            entityType: 'DeviceToken',
            entityId: $token->id,
            deskripsi: "Super Admin men-generate token API baru untuk perangkat '{$token->name}'"
        );

        return back()->with([
            'status' => "Token API baru untuk '{$token->name}' berhasil dibuat.",
            'generated_token' => $plainToken,
            'generated_name' => $token->name,
        ]);
    }

    /**
     * Regenerate token API yang sudah ada
     */
    public function regenerate(DeviceToken $token)
    {
        $plainToken = Str::random(40);

        $token->update([
            'token_hash' => DeviceToken::hashToken($plainToken),
        ]);

        AuditLog::record(
            action: 'regenerate_device_token',
            entityType: 'DeviceToken',
            entityId: $token->id,
            deskripsi: "Super Admin meregenerasi token API untuk perangkat '{$token->name}'"
        );

        return back()->with([
            'status' => "Token API untuk '{$token->name}' berhasil diperbarui.",
            'generated_token' => $plainToken,
            'generated_name' => $token->name,
        ]);
    }

    /**
     * Toggle status aktif / nonaktif penerimaan API
     */
    public function toggle(DeviceToken $token)
    {
        $token->is_active = ! $token->is_active;
        $token->save();

        $statusText = $token->is_active ? 'Aktif' : 'Nonaktif';

        AuditLog::record(
            action: 'toggle_device_token',
            entityType: 'DeviceToken',
            entityId: $token->id,
            deskripsi: "Super Admin mengubah status token perangkat '{$token->name}' menjadi {$statusText}"
        );

        return back()->with('status', "Status perangkat '{$token->name}' berhasil diubah menjadi {$statusText}.");
    }

    /**
     * Hapus token API perangkat
     */
    public function destroy(DeviceToken $token)
    {
        $name = $token->name;
        $id = $token->id;

        $token->delete();

        AuditLog::record(
            action: 'delete_device_token',
            entityType: 'DeviceToken',
            entityId: $id,
            deskripsi: "Super Admin menghapus token API perangkat '{$name}'"
        );

        return back()->with('status', "Token API perangkat '{$name}' berhasil dihapus.");
    }

    /**
     * Simpan pengaturan profil kartu yang diterima untuk E-KTP
     */
    public function updateCardProfile(Request $request)
    {
        $validated = $request->validate([
            'sak' => 'nullable|string|max:50',
            'uid_size' => 'nullable|integer|in:4,7,10',
            'card_type' => 'nullable|string|max:100',
        ]);

        $cleanSak = $request->filled('sak') ? trim($request->input('sak')) : null;
        $cleanUidSize = $request->filled('uid_size') ? (string) $request->input('uid_size') : null;
        $cleanCardType = $request->filled('card_type') ? trim($request->input('card_type')) : null;

        AppSetting::set('rfid_profile_sak', $cleanSak);
        AppSetting::set('rfid_profile_uid_size', $cleanUidSize);
        AppSetting::set('rfid_profile_card_type', $cleanCardType);

        AuditLog::record(
            action: 'update_rfid_card_profile',
            entityType: 'AppSetting',
            deskripsi: "Super Admin memperbarui profil kartu RFID yang diterima: SAK=" . ($cleanSak ?: 'Semua') . ", UID Size=" . ($cleanUidSize ?: 'Semua') . ", Card Type=" . ($cleanCardType ?: 'Semua')
        );

        return back()->with('status', 'Profil kartu yang diterima untuk E-KTP berhasil disimpan.');
    }
}
