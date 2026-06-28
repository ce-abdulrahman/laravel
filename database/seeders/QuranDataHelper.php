<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * QuranDataHelper Trait
 *
 * Provides shared Quran structural data and lookup helpers
 * for use across all memorization seeders.
 *
 * Quran standard: Kufi tradition (6236 ayahs total).
 * Global ayah IDs are sequential from 1 (Al-Fatiha:1) to 6236 (An-Nas:6).
 */
trait QuranDataHelper
{
    // ─────────────────────────────────────────────
    // QURAN STRUCTURE CONSTANTS
    // ─────────────────────────────────────────────

    /** @var array<int,int> Surah ID → ayah count (Kufi count, 6236 total) */
    private const SURAH_AYAH_COUNTS = [
        1  => 7,   2  => 286, 3  => 200, 4  => 176, 5  => 120,
        6  => 165, 7  => 206, 8  => 75,  9  => 129, 10 => 109,
        11 => 123, 12 => 111, 13 => 43,  14 => 52,  15 => 99,
        16 => 128, 17 => 111, 18 => 110, 19 => 98,  20 => 135,
        21 => 112, 22 => 78,  23 => 118, 24 => 64,  25 => 77,
        26 => 227, 27 => 93,  28 => 88,  29 => 69,  30 => 60,
        31 => 34,  32 => 30,  33 => 73,  34 => 54,  35 => 45,
        36 => 83,  37 => 182, 38 => 88,  39 => 75,  40 => 85,
        41 => 54,  42 => 53,  43 => 89,  44 => 59,  45 => 37,
        46 => 35,  47 => 38,  48 => 29,  49 => 18,  50 => 45,
        51 => 60,  52 => 49,  53 => 62,  54 => 55,  55 => 78,
        56 => 96,  57 => 29,  58 => 22,  59 => 24,  60 => 13,
        61 => 14,  62 => 11,  63 => 11,  64 => 18,  65 => 12,
        66 => 12,  67 => 30,  68 => 52,  69 => 52,  70 => 44,
        71 => 28,  72 => 28,  73 => 20,  74 => 56,  75 => 40,
        76 => 31,  77 => 50,  78 => 40,  79 => 46,  80 => 42,
        81 => 29,  82 => 19,  83 => 36,  84 => 25,  85 => 22,
        86 => 17,  87 => 19,  88 => 26,  89 => 30,  90 => 20,
        91 => 15,  92 => 21,  93 => 11,  94 => 8,   95 => 8,
        96 => 19,  97 => 5,   98 => 8,   99 => 8,   100 => 11,
        101 => 11, 102 => 8,  103 => 3,  104 => 9,  105 => 5,
        106 => 4,  107 => 7,  108 => 3,  109 => 6,  110 => 3,
        111 => 5,  112 => 4,  113 => 5,  114 => 6,
    ];

    /** @var array<int,string> Surah ID → transliterated name */
    private const SURAH_NAMES = [
        1   => 'Al-Fatiha',     2   => 'Al-Baqarah',    3   => 'Al-Imran',
        4   => 'An-Nisa',       5   => 'Al-Maidah',      6   => "Al-An'am",
        7   => "Al-A'raf",      8   => 'Al-Anfal',       9   => 'At-Tawbah',
        10  => 'Yunus',         11  => 'Hud',            12  => 'Yusuf',
        13  => "Ar-Ra'd",       14  => 'Ibrahim',        15  => 'Al-Hijr',
        16  => 'An-Nahl',       17  => "Al-Isra'",       18  => 'Al-Kahf',
        19  => 'Maryam',        20  => 'Ta-Ha',          21  => 'Al-Anbiya',
        22  => 'Al-Hajj',       23  => "Al-Mu'minun",    24  => 'An-Nur',
        25  => 'Al-Furqan',     26  => "Ash-Shu'ara'",   27  => 'An-Naml',
        28  => 'Al-Qasas',      29  => 'Al-Ankabut',     30  => 'Ar-Rum',
        31  => 'Luqman',        32  => 'As-Sajda',       33  => 'Al-Ahzab',
        34  => "Saba'",         35  => 'Fatir',          36  => 'Ya-Sin',
        37  => 'As-Saffat',     38  => 'Sad',            39  => 'Az-Zumar',
        40  => 'Ghafir',        41  => 'Fussilat',       42  => 'Ash-Shura',
        43  => 'Az-Zukhruf',    44  => 'Ad-Dukhan',      45  => 'Al-Jathiyah',
        46  => 'Al-Ahqaf',      47  => 'Muhammad',       48  => 'Al-Fath',
        49  => 'Al-Hujurat',    50  => 'Qaf',            51  => 'Adh-Dhariyat',
        52  => 'At-Tur',        53  => 'An-Najm',        54  => 'Al-Qamar',
        55  => 'Ar-Rahman',     56  => "Al-Waqi'ah",     57  => 'Al-Hadid',
        58  => 'Al-Mujadila',   59  => 'Al-Hashr',       60  => 'Al-Mumtahana',
        61  => 'As-Saf',        62  => "Al-Jumu'ah",     63  => 'Al-Munafiqun',
        64  => 'At-Taghabun',   65  => 'At-Talaq',       66  => 'At-Tahrim',
        67  => 'Al-Mulk',       68  => 'Al-Qalam',       69  => 'Al-Haqqah',
        70  => "Al-Ma'arij",    71  => 'Nuh',            72  => 'Al-Jinn',
        73  => 'Al-Muzzammil',  74  => 'Al-Muddaththir', 75  => 'Al-Qiyamah',
        76  => 'Al-Insan',      77  => 'Al-Mursalat',    78  => "An-Naba'",
        79  => "An-Nazi'at",    80  => "'Abasa",          81  => 'At-Takwir',
        82  => 'Al-Infitar',    83  => 'Al-Mutaffifin',  84  => 'Al-Inshiqaq',
        85  => 'Al-Buruj',      86  => 'At-Tariq',       87  => "Al-A'la",
        88  => 'Al-Ghashiyah',  89  => 'Al-Fajr',        90  => 'Al-Balad',
        91  => 'Ash-Shams',     92  => 'Al-Layl',        93  => 'Ad-Duha',
        94  => 'Ash-Sharh',     95  => 'At-Tin',         96  => 'Al-Alaq',
        97  => 'Al-Qadr',       98  => 'Al-Bayyinah',    99  => 'Az-Zalzalah',
        100 => "Al-'Adiyat",    101 => "Al-Qari'ah",     102 => 'At-Takathur',
        103 => 'Al-Asr',        104 => 'Al-Humazah',     105 => 'Al-Fil',
        106 => 'Quraysh',       107 => "Al-Ma'un",       108 => 'Al-Kawthar',
        109 => 'Al-Kafirun',    110 => 'An-Nasr',        111 => 'Al-Masad',
        112 => 'Al-Ikhlas',     113 => 'Al-Falaq',       114 => 'An-Nas',
    ];

    /**
     * Standard juz start positions.
     * Each juz begins at the given surah and ayah number (within that surah).
     *
     * @var array<int, array{surah:int, ayah:int}>
     */
    private const JUZ_STARTS = [
        1  => ['surah' => 1,  'ayah' => 1],
        2  => ['surah' => 2,  'ayah' => 142],
        3  => ['surah' => 2,  'ayah' => 253],
        4  => ['surah' => 3,  'ayah' => 93],
        5  => ['surah' => 4,  'ayah' => 24],
        6  => ['surah' => 4,  'ayah' => 148],
        7  => ['surah' => 5,  'ayah' => 82],
        8  => ['surah' => 6,  'ayah' => 111],
        9  => ['surah' => 7,  'ayah' => 88],
        10 => ['surah' => 8,  'ayah' => 41],
        11 => ['surah' => 9,  'ayah' => 93],
        12 => ['surah' => 11, 'ayah' => 6],
        13 => ['surah' => 12, 'ayah' => 53],
        14 => ['surah' => 15, 'ayah' => 1],
        15 => ['surah' => 17, 'ayah' => 1],
        16 => ['surah' => 18, 'ayah' => 75],
        17 => ['surah' => 21, 'ayah' => 1],
        18 => ['surah' => 23, 'ayah' => 1],
        19 => ['surah' => 25, 'ayah' => 21],
        20 => ['surah' => 27, 'ayah' => 56],
        21 => ['surah' => 29, 'ayah' => 46],
        22 => ['surah' => 33, 'ayah' => 31],
        23 => ['surah' => 36, 'ayah' => 28],
        24 => ['surah' => 39, 'ayah' => 32],
        25 => ['surah' => 41, 'ayah' => 47],
        26 => ['surah' => 46, 'ayah' => 1],
        27 => ['surah' => 51, 'ayah' => 31],
        28 => ['surah' => 58, 'ayah' => 1],
        29 => ['surah' => 67, 'ayah' => 1],
        30 => ['surah' => 78, 'ayah' => 1],
    ];

    // ─────────────────────────────────────────────
    // RUNTIME LOOKUP TABLES (built in initQuranData)
    // ─────────────────────────────────────────────

    /** @var array<int,int> Surah ID → first global ayah ID */
    private array $surahStartGlobalIds = [];

    /**
     * Global ayah ID → position details.
     * @var array<int, array{surah_id:int, ayah_num:int}>
     */
    private array $globalIdToSurah = [];

    /** @var array<int, array{start:int, end:int}> Juz ID → global ayah range */
    private array $juzGlobalRanges = [];

    // ─────────────────────────────────────────────
    // INITIALISER
    // ─────────────────────────────────────────────

    /**
     * Build all runtime lookup tables from the static constants.
     * Must be called once at the start of each seeder's run().
     */
    protected function initQuranData(): void
    {
        // Build surahStartGlobalIds + globalIdToSurah
        $running = 1;
        foreach (self::SURAH_AYAH_COUNTS as $surahId => $count) {
            $this->surahStartGlobalIds[$surahId] = $running;
            for ($i = 0; $i < $count; $i++) {
                $this->globalIdToSurah[$running + $i] = [
                    'surah_id' => $surahId,
                    'ayah_num' => $i + 1,
                ];
            }
            $running += $count;
        }

        // Build juzGlobalRanges
        $juzIds = array_keys(self::JUZ_STARTS);
        foreach ($juzIds as $idx => $juzId) {
            $startPos  = self::JUZ_STARTS[$juzId];
            $startGlob = $this->surahStartGlobalIds[$startPos['surah']] + $startPos['ayah'] - 1;

            if (isset($juzIds[$idx + 1])) {
                $nextPos  = self::JUZ_STARTS[$juzIds[$idx + 1]];
                $endGlob  = $this->surahStartGlobalIds[$nextPos['surah']] + $nextPos['ayah'] - 2;
            } else {
                $endGlob = 6236; // Last ayah of An-Nas
            }

            $this->juzGlobalRanges[$juzId] = ['start' => $startGlob, 'end' => $endGlob];
        }
    }

    // ─────────────────────────────────────────────
    // LOOKUP HELPERS
    // ─────────────────────────────────────────────

    /** Returns surah_id and within-surah ayah number for a global ayah ID. */
    protected function getSurahForGlobalId(int $globalId): array
    {
        return $this->globalIdToSurah[$globalId] ?? ['surah_id' => 1, 'ayah_num' => 1];
    }

    /** Converts a surah + within-surah ayah number to a global ayah ID. */
    protected function getGlobalId(int $surahId, int $ayahNum): int
    {
        return ($this->surahStartGlobalIds[$surahId] ?? 1) + $ayahNum - 1;
    }

    /** First global ayah ID of a surah. */
    protected function surahFirstGlobalId(int $surahId): int
    {
        return $this->surahStartGlobalIds[$surahId] ?? 1;
    }

    /** Last global ayah ID of a surah. */
    protected function surahLastGlobalId(int $surahId): int
    {
        $start = $this->surahStartGlobalIds[$surahId] ?? 1;
        $count = self::SURAH_AYAH_COUNTS[$surahId] ?? 1;
        return $start + $count - 1;
    }

    /** Ayah count for a given surah. */
    protected function surahAyahCount(int $surahId): int
    {
        return self::SURAH_AYAH_COUNTS[$surahId] ?? 0;
    }

    /** Human-readable name of a surah. */
    protected function surahName(int $surahId): string
    {
        return self::SURAH_NAMES[$surahId] ?? "Surah {$surahId}";
    }

    /** Global ayah range [start, end] for a juz. */
    protected function juzRange(int $juzId): array
    {
        return $this->juzGlobalRanges[$juzId] ?? ['start' => 1, 'end' => 6236];
    }

    // ─────────────────────────────────────────────
    // WEIGHTED RANDOM HELPER
    // ─────────────────────────────────────────────

    /**
     * Pick a random key from an associative weights map.
     * e.g. weightedRandom(['a' => 70, 'b' => 30]) → 'a' ~70% of the time.
     */
    protected function weightedRandom(array $weights): string
    {
        $total      = array_sum($weights);
        $rand       = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weights);
    }
}
