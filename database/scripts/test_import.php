<?php
/**
 * Add missing CSV cities and re-run full import.
 */
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Models\PrayerTime;

$missings = [
    ['name' => 'Kalar',      'lat' => 34.6314, 'lng' => 45.3228, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Khanaqin',   'lat' => 34.3379, 'lng' => 45.3705, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Kirkuk',     'lat' => 35.4681, 'lng' => 44.3922, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Koysinjaq',  'lat' => 36.0866, 'lng' => 44.6315, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Makhmur',    'lat' => 35.7706, 'lng' => 43.5843, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Qaladiza',   'lat' => 36.1781, 'lng' => 45.1240, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Qasre',      'lat' => 36.8500, 'lng' => 44.5000, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Gokhlan',    'lat' => 37.0000, 'lng' => 43.5000, 'timezone' => 'Asia/Baghdad'],
    ['name' => 'Tuz Khurma', 'lat' => 34.8871, 'lng' => 44.6390, 'timezone' => 'Asia/Baghdad'],
];

$now = now()->toDateTimeString();
foreach ($missings as $city) {
    DB::table('cities')->insertOrIgnore(array_merge($city, ['created_at' => $now, 'updated_at' => $now]));
}

echo "Cities in DB now: " . City::count() . "\n";
$names = City::orderBy('name')->pluck('name')->toArray();
echo "Names: " . implode(', ', $names) . "\n\n";

// Re-run full import
$csvPath = __DIR__ . '/../../../prayer_times.csv';
$year = 2026;
$months = [
    'Jan' => 1, 'Feb' => 2,  'Mar' => 3,  'Apr' => 4,
    'May' => 5, 'Jun' => 6,  'Jul' => 7,  'Aug' => 8,
    'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12,
];
$content = file_get_contents($csvPath);
$lines   = explode("\n", str_replace("\r\n", "\n", $content));
$cityMap = City::pluck('id', 'name')->mapWithKeys(fn($id, $n) => [strtolower($n) => $id])->toArray();
$data = []; $errors = []; $now = now()->toDateTimeString();
$padTime = fn($t) => (strlen($t) === 4 && $t[1] === ':') ? '0' . $t : $t;

foreach ($lines as $i => $line) {
    $line = trim($line);
    if ($i === 0 || !$line) continue;
    $cols = str_getcsv($line);
    if (count($cols) < 8) { $errors[] = "Row {$i}: cols=" . count($cols); continue; }
    [$cn, $rawDate, $fajr, $sunrise, $dhuhr, $asr, $maghrib, $isha] = array_map('trim', array_slice($cols, 0, 8));
    $ck = strtolower($cn);
    if (!isset($cityMap[$ck])) { $errors[] = "Row {$i}: unknown city '{$cn}'"; continue; }
    $parts = explode('-', $rawDate, 2);
    if (count($parts) !== 2) { $errors[] = "Row {$i}: bad date"; continue; }
    [$day, $ma] = $parts; $day = (int)$day; $month = $months[$ma] ?? null;
    if (!$month || !checkdate($month, $day, $year)) { $errors[] = "Row {$i}: invalid date '{$rawDate}'"; continue; }
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $data[] = [
        'city_id' => $cityMap[$ck], 'date' => $date, 'year' => $year,
        'fajr' => $padTime($fajr), 'sunrise' => $padTime($sunrise), 'dhuhr' => $padTime($dhuhr),
        'asr' => $padTime($asr), 'maghrib' => $padTime($maghrib), 'isha' => $padTime($isha),
        'source' => 'import', 'created_at' => $now, 'updated_at' => $now,
    ];
}
echo "Parsed: " . count($data) . " rows, errors: " . count($errors) . "\n";
if ($errors) echo "First error: " . $errors[0] . "\n";

DB::beginTransaction();
try {
    foreach (array_chunk($data, 500) as $chunk) {
        PrayerTime::upsert($chunk, ['city_id', 'date'], ['fajr','sunrise','dhuhr','asr','maghrib','isha','source','year','updated_at']);
    }
    DB::commit();
} catch (\Throwable $e) { DB::rollBack(); echo "ERROR: " . $e->getMessage() . "\n"; exit(1); }

echo "Total prayer_times rows: " . PrayerTime::count() . "\n";
$distinct = PrayerTime::distinct('city_id')->count('city_id');
echo "Cities with data: {$distinct}\n\n✅ Done!\n";
