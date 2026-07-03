<?php

$jsonPath = __DIR__ . '/../script_concatination/tajweed_rules_segment.json';

if (!file_exists($jsonPath)) {
    die("Error: tajweed_rules_segment.json not found at $jsonPath\n");
}

$jsonContent = file_get_contents($jsonPath);
$ayahs = json_decode($jsonContent, true);

echo "Total entries in JSON: " . count($ayahs) . "\n";

// Let's print the first 3 entries' metadata (without full segment arrays)
for ($i = 0; $i < min(3, count($ayahs)); $i++) {
    $ayah = $ayahs[$i];
    echo "\nEntry $i:\n";
    echo "  id: " . ($ayah['id'] ?? 'null') . "\n";
    echo "  surah_id: " . ($ayah['surah_id'] ?? 'null') . "\n";
    echo "  ayah_number: " . ($ayah['ayah_number'] ?? 'null') . "\n";
    echo "  segments count: " . (isset($ayah['tajweed_segments']) ? count($ayah['tajweed_segments']) : 0) . "\n";
    if (isset($ayah['tajweed_segments']) && count($ayah['tajweed_segments']) > 0) {
        echo "  first segment text: " . ($ayah['tajweed_segments'][0]['text_segment'] ?? 'null') . "\n";
    }
}
