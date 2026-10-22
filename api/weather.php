<?php
/**
 * BMKG Weather API Proxy & Cache for Lhokseumawe
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Daftar Kecamatan di Kota Lhokseumawe beserta kode adm4 perwakilan
$districts = [
    'banda_sakti' => [
        'name' => 'Banda Sakti',
        'sub' => 'Kuta Blang / Pusat Kota',
        'adm4' => '11.73.02.2001',
        'lat' => 5.1831,
        'lon' => 97.1363
    ],
    'muara_dua' => [
        'name' => 'Muara Dua',
        'sub' => 'Alue Awe / Cunda',
        'adm4' => '11.73.01.2002',
        'lat' => 5.1610,
        'lon' => 97.1420
    ],
    'muara_satu' => [
        'name' => 'Muara Satu',
        'sub' => 'Batuphat / Blang Panyang',
        'adm4' => '11.73.04.2001',
        'lat' => 5.2261,
        'lon' => 97.0511
    ],
    'blang_mangat' => [
        'name' => 'Blang Mangat',
        'sub' => 'Kuala / Punteuet',
        'adm4' => '11.73.03.2001',
        'lat' => 5.1455,
        'lon' => 97.1554
    ]
];

$action = $_GET['action'] ?? 'weather';

if ($action === 'districts') {
    echo json_encode([
        'status' => 'success',
        'data' => $districts
    ]);
    exit;
}

$districtKey = $_GET['district'] ?? 'banda_sakti';
if (!array_key_exists($districtKey, $districts)) {
    $districtKey = 'banda_sakti';
}

$selected = $districts[$districtKey];
$adm4 = $selected['adm4'];

// Direktori cache: gunakan /tmp jika di environment Vercel serverless, atau folder lokal
$cacheDir = function_exists('sys_get_temp_dir') && getenv('VERCEL') ? sys_get_temp_dir() . '/weather_cache' : __DIR__ . '/../cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

$cacheFile = $cacheDir . '/weather_' . $districtKey . '.json';
$cacheLifetime = 900; // 15 menit cache

$data = null;
$fromCache = false;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
    $content = @file_get_contents($cacheFile);
    if ($content) {
        $data = json_decode($content, true);
        if ($data) {
            $fromCache = true;
        }
    }
}

if (!$data) {
    $bmkgUrl = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=" . $adm4;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: LhokseumaweWeatherApp/1.0 (BMKG Client PHP)\r\nAccept: application/json\r\n",
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($bmkgUrl, false, $context);

    if ($response) {
        $parsed = json_decode($response, true);
        if ($parsed && !empty($parsed['data'])) {
            $data = $parsed;
            @file_put_contents($cacheFile, $response);
        }
    }

    // Jika gagal fetch live namun ada cache lama, gunakan cache cadangan
    if (!$data && file_exists($cacheFile)) {
        $content = @file_get_contents($cacheFile);
        $data = json_decode($content, true);
        $fromCache = true;
    }
}

if (!$data) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data cuaca dari server BMKG. Silakan coba beberapa saat lagi.'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'district_key' => $districtKey,
    'district_info' => $selected,
    'cached' => $fromCache,
    'cache_time' => file_exists($cacheFile) ? date('Y-m-d H:i:s', filemtime($cacheFile)) : date('Y-m-d H:i:s'),
    'raw' => $data
]);
