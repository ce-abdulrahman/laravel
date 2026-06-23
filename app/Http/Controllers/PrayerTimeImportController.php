<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PrayerTime;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrayerTimeImportController extends Controller
{
    // ─── Month map for CSV date parsing ──────────────────────────────────────

    private const MONTHS = [
        'Jan' => 1, 'Feb' => 2,  'Mar' => 3,  'Apr' => 4,
        'May' => 5, 'Jun' => 6,  'Jul' => 7,  'Aug' => 8,
        'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12,
    ];

    // ─── Show Import Form ─────────────────────────────────────────────────────

    public function showImport(): View
    {
        $this->authorizeAdmin();
        $cities = City::orderBy('name')->get();
        return view('prayer-times.import', compact('cities'));
    }

    // ─── Preview Import ───────────────────────────────────────────────────────

    /**
     * Parse the uploaded file and return a preview of the first 10 rows.
     * Stores the parsed data in the session for the commit step.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $year    = (int) $request->input('year');
        $content = file_get_contents($request->file('file')->getRealPath());
        $rows    = $this->parseCsv($content, $year);

        if (empty($rows['data'])) {
            return response()->json([
                'success' => false,
                'message' => 'No valid rows found in the file.',
                'errors'  => $rows['errors'],
            ]);
        }

        // Store in session for commit
        session(['prayer_import_data' => $rows['data'], 'prayer_import_year' => $year]);

        return response()->json([
            'success'     => true,
            'total_rows'  => count($rows['data']),
            'preview'     => array_slice($rows['data'], 0, 10),
            'errors'      => $rows['errors'],
            'parse_errors'=> count($rows['errors']),
            'year'        => $year,
        ]);
    }

    // ─── Commit Import ────────────────────────────────────────────────────────

    /**
     * Commit the session-stored rows into the database via upsert.
     */
    public function commitImport(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $importData = session('prayer_import_data', []);
        $year       = session('prayer_import_year');

        if (empty($importData)) {
            return redirect()->route('prayer-times.import')
                ->with('error', 'No import data found. Please upload a file first.');
        }

        $inserted = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            // Chunk to avoid memory issues on large imports
            foreach (array_chunk($importData, 500) as $chunk) {
                PrayerTime::upsert(
                    $chunk,
                    ['city_id', 'date'],          // unique columns
                    ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha', 'source', 'year', 'updated_at']
                );
                $inserted += count($chunk);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('prayer-times.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        // Invalidate API cache for all cities
        Cache::flush(); // or targeted: clear per city+year keys

        session()->forget(['prayer_import_data', 'prayer_import_year']);

        return redirect()->route('prayer-times.index')
            ->with('success', "Import complete! {$inserted} rows upserted for year {$year}.");
    }

    // ─── Export CSV ───────────────────────────────────────────────────────────

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $query = $this->buildExportQuery($request);
        $filename = 'prayer_times_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['city', 'date', 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha', 'source', 'year']);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->city->name ?? '',
                        $row->date?->format('j-M') ?? '',
                        $row->fajr,
                        $row->sunrise,
                        $row->dhuhr,
                        $row->asr,
                        $row->maghrib,
                        $row->isha,
                        $row->source,
                        $row->year,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Export JSON ─────────────────────────────────────────────────────────

    public function exportJson(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $query    = $this->buildExportQuery($request);
        $filename = 'prayer_times_' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($query) {
            echo '[';
            $first = true;

            $query->chunk(500, function ($rows) use (&$first) {
                foreach ($rows as $row) {
                    if (!$first) {
                        echo ',';
                    }
                    echo json_encode([
                        'city'    => $row->city->name ?? '',
                        'date'    => $row->date?->format('Y-m-d') ?? '',
                        'fajr'    => $row->fajr,
                        'sunrise' => $row->sunrise,
                        'dhuhr'   => $row->dhuhr,
                        'asr'     => $row->asr,
                        'maghrib' => $row->maghrib,
                        'isha'    => $row->isha,
                        'source'  => $row->source,
                        'year'    => $row->year,
                    ]);
                    $first = false;
                }
            });

            echo ']';
        }, $filename, ['Content-Type' => 'application/json']);
    }

    // ─── CSV Parser ───────────────────────────────────────────────────────────

    /**
     * Parse CSV content into upsert-ready rows.
     * Handles the "D-Mon" date format (e.g. "1-Jan", "15-Dec").
     */
    private function parseCsv(string $content, int $year): array
    {
        $lines  = explode("\n", str_replace("\r\n", "\n", $content));
        $data   = [];
        $errors = [];
        $now    = now()->toDateTimeString();

        // Build city lookup: name (lowercase) → id
        $cityMap = City::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])
            ->toArray();

        foreach ($lines as $lineIndex => $line) {
            $line = trim($line);

            if ($lineIndex === 0 || empty($line)) {
                continue; // skip header and empty lines
            }

            $cols = str_getcsv($line);

            if (count($cols) < 8) {
                $errors[] = "Row {$lineIndex}: insufficient columns (" . count($cols) . "). Expected 8.";
                continue;
            }

            [$cityName, $rawDate, $fajr, $sunrise, $dhuhr, $asr, $maghrib, $isha] = array_map('trim', array_slice($cols, 0, 8));

            // Resolve city
            $cityKey = strtolower($cityName);
            if (!isset($cityMap[$cityKey])) {
                $errors[] = "Row {$lineIndex}: unknown city '{$cityName}'.";
                continue;
            }
            $cityId = $cityMap[$cityKey];

            // Parse date: "D-Mon" → YYYY-MM-DD
            $parsedDate = $this->parseDate($rawDate, $year);
            if (!$parsedDate) {
                $errors[] = "Row {$lineIndex}: invalid date '{$rawDate}'.";
                continue;
            }

            // Validate time format
            if (!$this->validTime($fajr) || !$this->validTime($isha)) {
                $errors[] = "Row {$lineIndex}: invalid time values.";
                continue;
            }

            $data[] = [
                'city_id'    => $cityId,
                'date'       => $parsedDate,
                'year'       => $year,
                'fajr'       => $this->padTime($fajr),
                'sunrise'    => $this->padTime($sunrise),
                'dhuhr'      => $this->padTime($dhuhr),
                'asr'        => $this->padTime($asr),
                'maghrib'    => $this->padTime($maghrib),
                'isha'       => $this->padTime($isha),
                'source'     => 'import',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * Parse "D-Mon" format (e.g. "1-Jan", "31-Dec") into "YYYY-MM-DD".
     */
    private function parseDate(string $raw, int $year): ?string
    {
        // Format: "1-Jan", "15-Dec"
        $parts = explode('-', $raw, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$day, $monthAbbr] = $parts;
        $day = (int) $day;

        if ($day < 1 || $day > 31) {
            return null;
        }

        $month = self::MONTHS[$monthAbbr] ?? null;
        if (!$month) {
            return null;
        }

        // Validate the actual calendar date
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Validate time string format: H:MM or HH:MM.
     */
    private function validTime(string $t): bool
    {
        return (bool) preg_match('/^\d{1,2}:\d{2}$/', $t);
    }

    /**
     * Pad single-digit hour: "6:02" → "06:02".
     */
    private function padTime(string $t): string
    {
        if (strlen($t) === 4 && $t[1] === ':') {
            return '0' . $t;
        }
        return $t;
    }

    // ─── Export Query Builder ─────────────────────────────────────────────────

    private function buildExportQuery(Request $request)
    {
        $query = PrayerTime::with('city')->orderBy('city_id')->orderBy('date');

        if ($request->filled('city_id')) {
            $query->forCity((int) $request->city_id);
        }

        if ($request->filled('year')) {
            $query->forYear((int) $request->year);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->forDateRange($request->date_from, $request->date_to);
        }

        return $query;
    }

    // ─── Authorization ────────────────────────────────────────────────────────

    private function authorizeAdmin(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Admin access required.');
        }
    }
}
