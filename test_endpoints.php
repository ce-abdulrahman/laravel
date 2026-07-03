<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$endpoints = [
    '/api/surahs',
    '/api/settings',
    '/api/v1/surahs',
    '/api/v1/surahs/1/ayahs',
    '/api/v1/hadiths',
    '/api/v1/adhkars',
    '/api/v1/tasbihs',
    '/api/v1/settings',
];

echo "Testing endpoints memory usage:\n";
echo "---------------------------------\n";

foreach ($endpoints as $url) {
    // Reset memory peak
    if (function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
    }
    
    $memBefore = memory_get_usage(true);
    
    $request = Illuminate\Http\Request::create($url, 'GET');
    $response = $kernel->handle($request);
    
    $memAfter = memory_get_usage(true);
    $memPeak = memory_get_peak_usage(true);
    $contentLen = strlen($response->getContent());
    
    echo sprintf(
        "%-30s | Status: %d | Size: %10s bytes | Peak Mem: %6.2f MB | Current Mem: %6.2f MB\n",
        $url,
        $response->getStatusCode(),
        number_format($contentLen),
        $memPeak / 1024 / 1024,
        $memAfter / 1024 / 1024
    );
}
