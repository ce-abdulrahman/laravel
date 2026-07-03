<?php

$jsonPath = __DIR__ . '/../script_concatination/tajweed_rules_segment.json';
$jsonContent = file_get_contents($jsonPath);
$ayahs = json_decode($jsonContent, true);

// Let's sample entry 0, 10, 100, 1000
$indices = [0, 7, 8, 9, 10, 100, 1000];
foreach ($indices as $idx) {
    if (isset($ayahs[$idx])) {
        $a = $ayahs[$idx];
        echo "\nIndex $idx:\n";
        foreach ($a as $k => $v) {
            if ($k === 'tajweed_segments') {
                echo "  $k: Array of " . count($v) . " segments\n";
            } else {
                echo "  $k: " . (is_array($v) ? json_encode($v) : var_export($v, true)) . "\n";
            }
        }
    }
}
