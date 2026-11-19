<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\DeviceToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawToken = $request->header('X-Device-Token');

        // 1. Cek jika header X-Device-Token kosong
        if (empty($rawToken)) {
            AuditLog::record(
                action: 'rfid_token_rejected',
                entityType: 'DeviceToken',
                entityId: null,
                deskripsi: 'Percobaan scan RFID ditolak: Header X-Device-Token kosong'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid',
            ], 401);
        }

        // 2. Bandingkan hash dengan database memakai hash_equals
        $deviceToken = DeviceToken::findByPlainToken((string) $rawToken);

        // Jika token tidak cocok atau tidak terdaftar
        if (! $deviceToken) {
            AuditLog::record(
                action: 'rfid_token_rejected',
                entityType: 'DeviceToken',
                entityId: null,
                deskripsi: 'Percobaan scan RFID ditolak: Token salah atau tidak terdaftar'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid',
            ], 401);
        }

        // 3. Jika token nonaktif
        if (! $deviceToken->is_active) {
            AuditLog::record(
                action: 'rfid_token_rejected',
                entityType: 'DeviceToken',
                entityId: $deviceToken->id,
                deskripsi: "Percobaan scan RFID ditolak: Token perangkat '{$deviceToken->name}' berstatus nonaktif"
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid',
            ], 401);
        }

        // 4. Token valid: Update last_used_at lalu lanjut ke controller
        $deviceToken->update([
            'last_used_at' => now(),
        ]);

        $request->attributes->set('device_token', $deviceToken);

        return $next($request);
    }
}
