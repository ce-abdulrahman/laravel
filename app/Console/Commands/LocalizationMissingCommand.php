<?php

namespace App\Console\Commands;

use App\Services\MissingTranslationReportService;
use Illuminate\Console\Command;

class LocalizationMissingCommand extends Command
{
    protected $signature = 'localization:missing {--json= : Export the diagnostics report as JSON to the specified path} {--csv= : Export the diagnostics report as CSV to the specified path}';
    protected $description = 'Report missing, unused, and orphan translations in the system';

    protected MissingTranslationReportService $reportService;

    public function __construct(MissingTranslationReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle(): int
    {
        $this->info('🔍 Running Translation System Diagnostics...');

        $stats = $this->reportService->getReportStats();
        $missing = $this->reportService->getMissingTranslations();
        $unused = $this->reportService->getUnusedKeys();
        $orphans = $this->reportService->getOrphanTranslations();

        // 1. Display Statistics
        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('  📊  Translation Metrics & Coverage');
        $this->info('═══════════════════════════════════════════════');
        $this->line("  Total Keys           : " . $stats['total_keys']);
        $this->line("  Active Languages     : " . $stats['active_languages_count'] . " (" . implode(', ', array_map('strtoupper', $stats['active_locales'])) . ")");
        $this->line("  Coverage             : " . number_format($stats['coverage_percentage'], 2) . "%");
        $this->line("  Fully Translated Keys: " . $stats['fully_translated_keys']);
        $this->line("  Missing Units Count  : " . $stats['missing_translations']);
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        // 2. Display Missing Translations
        if (count($missing) > 0) {
            $this->warn('⚠️  Keys Missing Translations (Partial or Empty):');
            $rows = [];
            foreach (array_slice($missing, 0, 30) as $key) {
                $rows[] = [$key['key'], $key['group']];
            }
            $this->table(['Key', 'Group'], $rows);
            if (count($missing) > 30) {
                $this->line('  ... and ' . (count($missing) - 30) . ' more keys');
            }
            $this->newLine();
        } else {
            $this->info('✅ No missing translations detected. All active languages have values!');
            $this->newLine();
        }

        // 3. Display Unused Keys
        if (count($unused) > 0) {
            $this->warn('🗑️  Unused Keys (Defined in DB but not found in codebase):');
            $rows = [];
            foreach (array_slice($unused, 0, 30) as $key) {
                $rows[] = [$key['key'], $key['group']];
            }
            $this->table(['Key', 'Group'], $rows);
            if (count($unused) > 30) {
                $this->line('  ... and ' . (count($unused) - 30) . ' more keys');
            }
            $this->newLine();
        } else {
            $this->info('✅ No unused keys detected. All registered keys are referenced in code.');
            $this->newLine();
        }

        // 4. Display Orphan Translations
        if (count($orphans) > 0) {
            $this->warn('❌ Orphan Translation Rows (No matching parent key record):');
            $rows = [];
            foreach (array_slice($orphans, 0, 30) as $trans) {
                $rows[] = [$trans['id'], $trans['language']['code'] ?? 'Unknown', substr($trans['value'] ?? '', 0, 50)];
            }
            $this->table(['ID', 'Locale', 'Value Snippet'], $rows);
            if (count($orphans) > 30) {
                $this->line('  ... and ' . (count($orphans) - 30) . ' more rows');
            }
            $this->newLine();
        } else {
            $this->info('✅ No orphan translations found.');
            $this->newLine();
        }

        if ($jsonPath = $this->option('json')) {
            $jsonData = json_encode([
                'stats' => $stats,
                'missing_keys' => $missing,
                'unused_keys' => $unused,
                'orphan_translations' => $orphans,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($jsonPath, $jsonData);
            $this->info("💾 JSON report exported to: {$jsonPath}");
        }

        if ($csvPath = $this->option('csv')) {
            $fp = fopen('php://temp', 'r+');
            fputcsv($fp, ['Issue Type', 'Key', 'Group', 'Detail/Value']);
            foreach ($missing as $key) {
                fputcsv($fp, ['Missing Translation', $key['key'], $key['group'], '']);
            }
            foreach ($unused as $key) {
                fputcsv($fp, ['Unused Key', $key['key'], $key['group'], '']);
            }
            foreach ($orphans as $trans) {
                fputcsv($fp, ['Orphan Translation', '', '', 'id: ' . $trans['id'] . ', locale: ' . ($trans['language']['code'] ?? 'Unknown') . ', value: ' . ($trans['value'] ?? '')]);
            }
            rewind($fp);
            $csvData = stream_get_contents($fp);
            fclose($fp);
            file_put_contents($csvPath, $csvData);
            $this->info("💾 CSV report exported to: {$csvPath}");
        }

        return self::SUCCESS;
    }
}
