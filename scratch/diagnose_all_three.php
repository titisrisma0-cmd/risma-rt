<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\LayananMasyarakatController;
use App\Http\Controllers\RtVerifikasiController;
use Illuminate\Http\Request;

$u = User::where('email', 'rt01@rw-smart.test')->first() ?? User::role('rt_01')->first() ?? User::where('role', 'ketua_rt')->first();
Auth::login($u);

echo "Diagnosing modules for user: {$u->name} ({$u->email}), Role: " . ($u->role?->nama_role ?? 'none') . "\n\n";

// 1. Diagnose Posyandu RT
echo "--- 1. Testing Posyandu RT Controller & View ---\n";
try {
    $controller = app()->make(LayananMasyarakatController::class);
    $req = Request::create('/layanan/posyandu', 'GET');
    $res = $controller->posyandu($req);
    echo "Controller returned view: " . $res->name() . "\n";
    $html = $res->render();
    echo "View successfully rendered! Length: " . strlen($html) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR IN POSYANDU: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 2. Diagnose Keamanan & Ronda RT
echo "\n--- 2. Testing Keamanan & Ronda RT Controller & View ---\n";
try {
    $controller = app()->make(LayananMasyarakatController::class);
    $req = Request::create('/layanan/ronda', 'GET');
    $res = $controller->ronda($req);
    echo "Controller returned view: " . $res->name() . "\n";
    $html = $res->render();
    echo "View successfully rendered! Length: " . strlen($html) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR IN RONDA: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 3. Diagnose Manajemen Permohonan
echo "\n--- 3. Testing Manajemen Permohonan Controller & View ---\n";
try {
    $controller = app()->make(RtVerifikasiController::class);
    $req = Request::create('/rt/verifikasi/permohonan', 'GET');
    $res = $controller->permohonan($req);
    echo "Controller returned view: " . $res->name() . "\n";
    $html = $res->render();
    echo "View successfully rendered! Length: " . strlen($html) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR IN PERMOHONAN: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
