<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$u = User::where('email', 'rt01@rw-smart.test')->first() ?? User::role('rt_01')->first() ?? User::where('role', 'ketua_rt')->first();
Auth::login($u);

$urls = [
    'Posyandu RT' => '/posyandu',
    'Keamanan & Ronda RT' => '/ronda',
    'Manajemen Permohonan' => '/rt/verifikasi/permohonan',
];

echo "Testing as User: {$u->name} ({$u->email}), Role: {$u->role}\n\n";

foreach ($urls as $name => $url) {
    echo "--- Testing $name ($url) ---\n";
    try {
        $req = Request::create($url, 'GET');
        $res = $kernel->handle($req);
        $status = $res->getStatusCode();
        echo "Status: $status\n";
        if ($status >= 400) {
            $content = $res->getContent();
            // find title or exception in html
            if (preg_match('/<title>(.*?)<\/title>/is', $content, $m)) {
                echo "Title: " . trim($m[1]) . "\n";
            }
            if (preg_match('/<span class="text-muted">(.*?)<\/span>/is', $content, $m)) {
                echo "Detail: " . trim($m[1]) . "\n";
            }
        }
    } catch (\Throwable $e) {
        $real = $e;
        while ($real->getPrevious()) {
            $real = $real->getPrevious();
        }
        echo "EXCEPTION: " . $real->getMessage() . "\n";
        echo "Class: " . get_class($real) . "\n";
        echo "At: " . $real->getFile() . ":" . $real->getLine() . "\n";
    }
    echo "\n";
}
