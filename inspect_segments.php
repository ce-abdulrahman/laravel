<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/surahs/1/ayahs', 'GET');
$response = $kernel->handle($request);

$data = json_decode($response->getContent(), true);
$ayahs = $data['data']['data'] ?? [];

if (count($ayahs) > 0) {
    $firstAyah = $ayahs[0];
    $segments = $firstAyah['tajweed_segments'] ?? [];
    echo "Total segments in first Ayah: " . count($segments) . "\n";
    if (count($segments) > 0) {
        $firstSegment = $segments[0];
        echo "First segment keys: " . implode(', ', array_keys($firstSegment)) . "\n";
        
        // Print the first segment as JSON but truncate long values
        foreach ($firstSegment as $k => $v) {
            $serialized = json_encode($v);
            echo "Key: $k | Size: " . strlen($serialized) . " bytes | Value (truncated): " . substr($serialized, 0, 100) . "\n";
        }
    }
}
