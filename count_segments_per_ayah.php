<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total = DB::table('ayah_tajweed_segments')->count();
$distinctAyahs = DB::table('ayah_tajweed_segments')->distinct('ayah_id')->count('ayah_id');

echo "Total segments in DB: $total\n";
echo "Distinct Ayahs with segments: $distinctAyahs\n";

$duplicates = DB::table('ayah_tajweed_segments')
    ->select('ayah_id', 'tajweed_rule_id', 'start_index', 'end_index', 'matched_text', DB::raw('count(*) as count'))
    ->groupBy('ayah_id', 'tajweed_rule_id', 'start_index', 'end_index', 'matched_text')
    ->having('count', '>', 1)
    ->orderByDesc('count')
    ->limit(10)
    ->get();

echo "Top 10 Duplicate segments:\n";
foreach ($duplicates as $d) {
    echo "  Ayah ID: {$d->ayah_id} | Rule ID: {$d->tajweed_rule_id} | Start: {$d->start_index} | End: {$d->end_index} | Text: '{$d->matched_text}' | Count: {$d->count}\n";
}

$totalDuplicates = DB::table(DB::raw('(SELECT count(*) as count FROM ayah_tajweed_segments GROUP BY ayah_id, tajweed_rule_id, start_index, end_index, matched_text HAVING count > 1) as sub'))
    ->sum('count');
echo "Total duplicate rows count: $totalDuplicates\n";
