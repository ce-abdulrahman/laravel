<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$minId = DB::table('ayahs')->min('id');
$maxId = DB::table('ayahs')->max('id');
$count = DB::table('ayahs')->count();

echo "Ayahs Table Info:\n";
echo "  Min ID: $minId\n";
echo "  Max ID: $maxId\n";
echo "  Count: $count\n";

$sample = DB::table('ayahs')->select('id', 'surah_id', 'ayah_number')->orderBy('id')->limit(5)->get();
echo "\nFirst 5 Ayahs:\n";
foreach ($sample as $s) {
    echo "  ID: {$s->id} | Surah ID: {$s->surah_id} | Ayah Number: {$s->ayah_number}\n";
}

$sampleLast = DB::table('ayahs')->select('id', 'surah_id', 'ayah_number')->orderByDesc('id')->limit(5)->get();
echo "\nLast 5 Ayahs:\n";
foreach ($sampleLast as $s) {
    echo "  ID: {$s->id} | Surah ID: {$s->surah_id} | Ayah Number: {$s->ayah_number}\n";
}
