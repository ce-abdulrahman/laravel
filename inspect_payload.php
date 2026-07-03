<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/surahs/1/ayahs', 'GET');
$response = $kernel->handle($request);

$jsonString = $response->getContent();
$data = json_decode($jsonString, true);

echo "JSON String Length: " . strlen($jsonString) . "\n";
echo "JSON Keys: " . implode(', ', array_keys($data)) . "\n";
echo "Status: " . $data['status'] . "\n";

$ayahs = $data['data']['data'] ?? [];
echo "Ayahs Count: " . count($ayahs) . "\n";

if (count($ayahs) > 0) {
    $firstAyah = $ayahs[0];
    echo "First Ayah keys: " . implode(', ', array_keys($firstAyah)) . "\n";
    
    // Check sizes of the keys
    foreach ($firstAyah as $key => $val) {
        $serialized = json_encode($val);
        echo "  - Key: $key | Size: " . strlen($serialized) . " bytes\n";
    }
}
