<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Migrasikan token lama dari .env (RFID_DEVICE_TOKEN) ke tabel device_tokens
     */
    public function run(): void
    {
        $legacyToken = env('RFID_DEVICE_TOKEN', 'smart-rw-iot-secret-key-2026');

        if (!empty($legacyToken)) {
            $tokenHash = DeviceToken::hashToken($legacyToken);
            $superAdmin = User::whereHas('role', function ($q) {
                $q->where('nama_role', 'superadmin');
            })->first();

            DeviceToken::firstOrCreate(
                ['token_hash' => $tokenHash],
                [
                    'name' => 'ESP32 Gateway Pos Ronda Default',
                    'is_active' => true,
                    'created_by' => $superAdmin?->id,
                ]
            );
        }
    }
}
