<?php

$jsonPath = __DIR__ . '/../script_concatination/tajweed_rules_segment.json';
$jsonContent = file_get_contents($jsonPath);
$ayahs = json_decode($jsonContent, true);

$nonEmptyCount = 0;
$emptyCount = 0;
$maxIdWithSegments = 0;

foreach ($ayahs as $a) {
    if (!empty($a['tajweed_segments'])) {
        $nonEmptyCount++;
        $maxIdWithSegments = max($maxIdWithSegments, $a['id']);
    } else {
        $emptyCount++;
    }
}

echo "JSON Analysis:\n";
echo "  Total entries: " . count($ayahs) . "\n";
echo "  Entries with segments: $nonEmptyCount\n";
echo "  Entries without segments: $emptyCount\n";
echo "  Max ID with segments: $maxIdWithSegments\n";
