<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$client = new Client([
    'base_uri' => 'http://localhost:8000',
    'cookies' => true,
    'http_errors' => false,
    'allow_redirects' => true,
]);

$cookieJar = new CookieJar();

echo "=== TESTING BROWSER HTTP REQUESTS ON LOCALHOST:8000 ===\n\n";

// 1. Get Login Page & CSRF Token
$resLoginGet = $client->get('/login', ['cookies' => $cookieJar]);
$htmlLogin = (string) $resLoginGet->getBody();
preg_match('/name="_token" value="([^"]+)"/', $htmlLogin, $matches);
$csrfToken = $matches[1] ?? '';
echo "1. GET /login -> Status: " . $resLoginGet->getStatusCode() . " (CSRF: " . substr($csrfToken, 0, 10) . "...)\n";

// 2. POST /login as RT 01
$resLoginPost = $client->post('/login', [
    'cookies' => $cookieJar,
    'form_params' => [
        '_token' => $csrfToken,
        'email' => 'rt01@rw-smart.test',
        'password' => 'password123',
    ],
]);
echo "2. POST /login (rt01@rw-smart.test) -> Status: " . $resLoginPost->getStatusCode() . "\n";

// 3. Test Module 1: Posyandu RT
$resPosyandu1 = $client->get('/layanan/posyandu', ['cookies' => $cookieJar]);
echo "3. GET /layanan/posyandu -> Status: " . $resPosyandu1->getStatusCode() . " (Size: " . strlen((string)$resPosyandu1->getBody()) . " bytes)\n";

$resPosyandu2 = $client->get('/posyandu', ['cookies' => $cookieJar]);
echo "   GET /posyandu (Redirect alias) -> Status: " . $resPosyandu2->getStatusCode() . " (Final URL: /layanan/posyandu)\n";

// 4. Test Module 2: Keamanan & Ronda RT
$resRonda1 = $client->get('/layanan/ronda', ['cookies' => $cookieJar]);
echo "4. GET /layanan/ronda -> Status: " . $resRonda1->getStatusCode() . " (Size: " . strlen((string)$resRonda1->getBody()) . " bytes)\n";

$resRonda2 = $client->get('/ronda', ['cookies' => $cookieJar]);
echo "   GET /ronda (Redirect alias) -> Status: " . $resRonda2->getStatusCode() . " (Final URL: /layanan/ronda)\n";

// 5. Test Module 3: Manajemen Permohonan
$resPermohonan = $client->get('/rt/verifikasi/permohonan', ['cookies' => $cookieJar]);
echo "5. GET /rt/verifikasi/permohonan -> Status: " . $resPermohonan->getStatusCode() . " (Size: " . strlen((string)$resPermohonan->getBody()) . " bytes)\n";

echo "\n=== ALL HTTP CHECKS COMPLETED ===\n";
