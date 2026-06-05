<?php

namespace Database\Seeders;

use App\Models\Ayah;
use App\Models\TajweedRule;
use App\Models\AyahTajweedSegment;
use Illuminate\Database\Seeder;

class TajweedSegmentSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing segments to avoid duplicates
        AyahTajweedSegment::truncate();

        // Rules mapping by slug to model id
        $ruleMap = TajweedRule::pluck('id', 'slug')->toArray();

        // Segments configuration
        // Array of: [surah_id, ayah_number, search_term, rule_slug, note]
        $segmentsToSeed = [
            // --- Surah 1 (Al-Fatihah) ---
            [1, 1, 'الرَّحْمَٰنِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 1, 'الرَّحِيمِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 2, 'الْعَالَمِينَ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 3, 'الرَّحْمَٰنِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 3, 'الرَّحِيمِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 4, 'الدِّينِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 5, 'نَسْتَعِينُ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 5, 'إِيَّاكَ', 'madd-munfasil', 'Madd Munfasil'],
            [1, 6, 'الْمُسْتَقِيمَ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 7, 'أَنْعَمْتَ', 'izhar', 'Izhar'],
            [1, 7, 'الَّذِينَ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 7, 'الْمَغْضُوبِ', 'madd-tabii', 'Madd Tabi\'i'],
            [1, 7, 'الضَّالِّينَ', 'madd-muttasil', 'Madd Muttasil (Madd Laazim)'],

            // --- Surah 2 (Al-Baqarah) ---
            [2, 2, 'الْكِتَابُ', 'madd-tabii', 'Madd Tabi\'i'],
            [2, 2, 'لِلْمُتَّقِينَ', 'madd-tabii', 'Madd Tabi\'i'],
            [2, 2, 'هُدًى لِلْمُتَّقِينَ', 'idgham', 'Idgham without Ghunnah'],

            [2, 3, 'يُنْفِقُونَ', 'ikhfa', 'Ikhfa'],
            [2, 3, 'وَمِمَّا', 'ghunnah', 'Ghunnah mushaddadah'],
            [2, 3, 'رَزَقْنَاهُمْ', 'qalqalah', 'Qalqalah'],
            [2, 3, 'يُؤْمِنُونَ', 'madd-tabii', 'Madd Tabi\'i'],
            [2, 3, 'وَيُقِيمُونَ', 'madd-tabii', 'Madd Tabi\'i'],
            [2, 3, 'يُنْفِقُونَ', 'madd-tabii', 'Madd Tabi\'i'],

            [2, 4, 'بِمَا أُنْزِلَ', 'madd-munfasil', 'Madd Munfasil'],
            [2, 4, 'وَمَا أُنْزِلَ', 'madd-munfasil', 'Madd Munfasil'],
            [2, 4, 'أُنْزِلَ', 'ikhfa', 'Ikhfa'],
            [2, 4, 'مِنْ قَبْلِكَ', 'ikhfa', 'Ikhfa'],
            [2, 4, 'قَبْلِكَ', 'qalqalah', 'Qalqalah'],
            [2, 4, 'يُوقِنُونَ', 'madd-tabii', 'Madd Tabi\'i'],

            [2, 5, 'أُولَٰئِكَ', 'madd-muttasil', 'Madd Muttasil'],
            [2, 5, 'هُدًى مِنْ', 'idgham', 'Idgham with Ghunnah'],
            [2, 5, 'مِنْ رَبِّهِمْ', 'idgham', 'Idgham without Ghunnah'],
            [2, 5, 'الْمُفْلِحُونَ', 'madd-tabii', 'Madd Tabi\'i'],
        ];

        foreach ($segmentsToSeed as $seg) {
            [$surahId, $ayahNum, $searchTerm, $ruleSlug, $note] = $seg;

            // Find Ayah
            $ayah = Ayah::where('surah_id', $surahId)
                        ->where('ayah_number', $ayahNum)
                        ->first();

            if (!$ayah) {
                $this->command->warn("Ayah $surahId:$ayahNum not found. Skipping.");
                continue;
            }

            // Find rule ID
            $ruleId = $ruleMap[$ruleSlug] ?? null;
            if (!$ruleId) {
                $this->command->warn("Tajweed rule with slug '$ruleSlug' not found. Skipping.");
                continue;
            }

            // Calculate start and end indices using mb functions
            $pos = mb_strpos($ayah->text_uthmani, $searchTerm);
            if ($pos === false) {
                $this->command->warn("Search term '$searchTerm' not found in Ayah $surahId:$ayahNum ('{$ayah->text_uthmani}'). Skipping.");
                continue;
            }

            $length = mb_strlen($searchTerm);

            AyahTajweedSegment::create([
                'ayah_id' => $ayah->id,
                'tajweed_rule_id' => $ruleId,
                'text_segment' => $searchTerm,
                'start_index' => $pos,
                'end_index' => $pos + $length,
                'note' => $note,
            ]);
        }

        $this->command->info('Tajweed segments seeded successfully.');
    }
}
