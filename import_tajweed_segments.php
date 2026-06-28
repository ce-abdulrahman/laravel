<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ayah;
use App\Models\TajweedRule;
use Illuminate\Support\Facades\DB;

$jsonPath = __DIR__ . '/../script_concatination/tajweed_rules_segment.json';

if (!file_exists($jsonPath)) {
    die("Error: tajweed_rules_segment.json not found at $jsonPath\n");
}

echo "Reading tajweed_rules_segment.json...\n";
$jsonContent = file_get_contents($jsonPath);
$ayahs = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg() . "\n");
}

echo "Preloading DB Ayah mapping and Tajweed Rules...\n";
$ayahMap = Ayah::pluck('surah_id', 'id')->toArray();
$dbRules = TajweedRule::pluck('id', 'slug')->toArray();

// Map from script slugs to DB slugs
$slugMapping = [
    'izhar-halqi' => 'idhhar-halqi',
    'idgham-noon-sakinah' => 'idgham-halqi',
    'iqlab' => 'iqlab',
    'ikhfa-haqiqi' => 'ikhfa-haqiqi',
    'shadhdh' => 'words-with-idgham',
    'ikhfa-shafawi' => 'ikhfa-shafawi',
    'idgham-shafawi' => 'idgham-shafawi',
    'idhhar-shafawi' => 'idhhar-shafawi',
    'madd-muttasil' => 'madd-muttasil',
    'madd-munfasil' => 'madd-munfasil',
    'madd-badal' => 'madd-badal',
    'madd-aridh' => 'madd-aridh',
    'madd-leen' => 'madd-leen',
    'madd-lazim-kalimi-muthaqqal' => 'madd-lazim-kalimi-muthaqqal',
    'madd-lazim-kalimi-mukhaffaf' => 'madd-lazim-kalimi-mukhaffaf',
    'madd-lazim-harfi-muthaqqal' => 'madd-lazim-harfi-muthaqqal',
    'madd-lazim-harfi-mukhaffaf' => 'madd-lazim-harfi-mukhaffaf',
    'madd-silah-kubra' => 'madd-silah-kubra',
    'madd-silah-sughra' => 'madd-silah-sughra',
    'madd-iwad' => 'madd-iwad',
    'raa-tafkhim' => 'raa-tafkhim',
    'raa-tarqiq' => 'raa-tarqiq',
    'raa-jawaz' => 'raa-jawaz',
    'qalqalah-kubra' => 'qalqalah-kubra',
    'qalqalah-sughra' => 'qalqalah-sughra',
];

echo "Truncating existing ayah_tajweed_segments table...\n";
DB::table('ayah_tajweed_segments')->truncate();

echo "Importing new segments...\n";
$insertData = [];
$totalImported = 0;

foreach ($ayahs as $ayahData) {
    $ayahId = $ayahData['id'];
    $surahId = $ayahMap[$ayahId] ?? null;
    
    if (!$surahId) {
        continue;
    }

    if (empty($ayahData['tajweed_segments'])) {
        continue;
    }

    foreach ($ayahData['tajweed_segments'] as $seg) {
        $jsonSlug = $seg['rule']['slug'] ?? '';
        $dbSlug = $slugMapping[$jsonSlug] ?? $jsonSlug;
        $ruleId = $dbRules[$dbSlug] ?? null;

        if (!$ruleId) {
            echo "Warning: Rule slug '$jsonSlug' (mapped to '$dbSlug') not found in DB rules list.\n";
            continue;
        }

        $insertData[] = [
            'surah_id' => $surahId,
            'ayah_id' => $ayahId,
            'tajweed_rule_id' => $ruleId,
            'matched_text' => $seg['text_segment'] ?? '',
            'start_index' => $seg['start_index'] ?? 0,
            'end_index' => $seg['end_index'] ?? 0,
            'note' => $seg['note'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (count($insertData) >= 2000) {
            DB::table('ayah_tajweed_segments')->insert($insertData);
            $totalImported += count($insertData);
            $insertData = [];
        }
    }
}

if (!empty($insertData)) {
    DB::table('ayah_tajweed_segments')->insert($insertData);
    $totalImported += count($insertData);
}

echo "Successfully imported $totalImported tajweed segments to database!\n";
