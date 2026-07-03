<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$segments = DB::table('ayah_tajweed_segments')
    ->where('ayah_id', 1)
    ->get();

echo "Total segments for Ayah 1: " . count($segments) . "\n";

// Count by rule
$byRule = [];
foreach ($segments as $s) {
    $byRule[$s->tajweed_rule_id] = ($byRule[$s->tajweed_rule_id] ?? 0) + 1;
}
echo "\nSegments by Rule ID:\n";
foreach ($byRule as $rid => $count) {
    echo "  Rule ID: $rid | Count: $count\n";
}

// Let's print the first 20 segments
echo "\nFirst 20 segments:\n";
for ($i = 0; $i < min(20, count($segments)); $i++) {
    $s = $segments[$i];
    echo "  Rule: {$s->tajweed_rule_id} | Start: {$s->start_index} | End: {$s->end_index} | Text: '{$s->matched_text}'\n";
}
