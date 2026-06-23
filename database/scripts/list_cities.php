<?php
$content = file_get_contents(__DIR__ . '/../../../prayer_times.csv');
$lines = explode("\n", str_replace("\r\n", "\n", $content));
$cities = [];
foreach ($lines as $i => $line) {
    if ($i === 0 || !trim($line)) continue;
    $cols = str_getcsv(trim($line));
    if (isset($cols[0])) $cities[trim($cols[0])] = 1;
}
ksort($cities);
foreach (array_keys($cities) as $city) {
    echo $city . "\n";
}
echo "\nTotal cities: " . count($cities) . "\n";
