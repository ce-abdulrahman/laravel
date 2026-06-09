<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TajweedRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // ─────────────────────────────────────────────────────────────────
        // Build slug → id map from the tajweed_rule_categories table
        // using a single query + foreach loop (clean & efficient)
        // ─────────────────────────────────────────────────────────────────
        $categoryMap = []; // slug => id

        foreach (DB::table('tajweed_rule_categories')->get(['id', 'slug']) as $cat) {
            $categoryMap[$cat->slug] = $cat->id;
        }

        // Helper closure: resolve slug to category ID (or null)
        $cat = fn(string $slug): ?int => $categoryMap[$slug] ?? null;

        // ─────────────────────────────────────────────────────────────────
        // Rule definitions — each row uses tajweed_rule_category_id directly
        // ─────────────────────────────────────────────────────────────────
        $rules = [
            // ==========================================
            // 1. NOON SAKINAH & TANWEEN
            // ==========================================
            [
                'name'                     => 'Idhhar Halqi (Clear)',
                'slug'                     => 'idhhar-halqi',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'               => '#000000',
                'description'              => 'Pronouncing the Noon Sakinah or Tanween clearly without extra Ghunnah when followed by throat letters (ء, ه, ع, ح, غ, خ).',
                'example_text'             => 'مِنْ خَوْفٍ',
                'priority'                 => 10,
            ],
            [
                'name'                     => 'Idgham Bighunnah (Merge with Nasal)',
                'slug'                     => 'idgham-bighunnah',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'               => '#4CAF50',
                'description'              => 'Merging the Noon into the following letter with a 2-beat nasal hold. Letters: ي, ن, م, و.',
                'example_text'             => 'مَن يَعْمَلْ',
                'priority'                 => 11,
            ],
            [
                'name'                     => 'Idgham Bighayr Ghunnah (Merge without Nasal)',
                'slug'                     => 'idgham-bighayr-ghunnah',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'               => '#9E9E9E',
                'description'              => 'Merging the Noon completely into the following letter without any nasal hold. Letters: ل, ر.',
                'example_text'             => 'مِن رَّبِّهِمْ',
                'priority'                 => 12,
            ],
            [
                'name'                     => 'Iqlab (Changing)',
                'slug'                     => 'iqlab',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'               => '#2196F3',
                'description'              => 'Changing the Noon sound into a hidden Meem with a 2-beat Ghunnah when followed by Ba (ب).',
                'example_text'             => 'مِن بَعْدِ',
                'priority'                 => 13,
            ],
            [
                'name'                     => 'Ikhfa Haqiqi (Hiding)',
                'slug'                     => 'ikhfa-haqiqi',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'               => '#4CAF50',
                'description'              => 'Hiding the Noon sound in the nasal cavity with a 2-beat hold before the remaining 15 letters.',
                'example_text'             => 'مِن قَبْلُ',
                'priority'                 => 14,
            ],

            // ==========================================
            // 2. MEEM SAKINAH
            // ==========================================
            [
                'name'                     => 'Ikhfa Shafawi',
                'slug'                     => 'ikhfa-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'               => '#4CAF50',
                'description'              => 'Hiding the Meem lightly with a 2-beat Ghunnah when followed by Ba (ب).',
                'example_text'             => 'تَرْمِيهِم بِحِجَارَةٍ',
                'priority'                 => 20,
            ],
            [
                'name'                     => 'Idgham Shafawi',
                'slug'                     => 'idgham-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'               => '#4CAF50',
                'description'              => 'Merging the Meem into another Meem with a 2-beat Ghunnah.',
                'example_text'             => 'لَهُم مَّثَلًا',
                'priority'                 => 21,
            ],
            [
                'name'                     => 'Idhhar Shafawi',
                'slug'                     => 'idhhar-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'               => '#000000',
                'description'              => 'Pronouncing the Meem clearly with closed lips before any letter other than Meem or Ba.',
                'example_text'             => 'أَلَمْ تَرَ',
                'priority'                 => 22,
            ],

            // ==========================================
            // 3. MADD (PROLONGATION)
            // ==========================================
            [
                'name'                     => 'Madd Tabii (Natural Madd)',
                'slug'                     => 'madd-tabii',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'The natural 2-beat prolongation of Alif, Waw, or Yaa when not followed by a Hamzah or Sukoon.',
                'example_text'             => 'نُوحِيهَا',
                'priority'                 => 30,
            ],
            [
                'name'                     => 'Madd Muttasil (Attached)',
                'slug'                     => 'madd-muttasil',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#E53935',
                'description'              => 'A mandatory 4 or 5 beat prolongation when a Madd letter is followed by a Hamzah in the same word.',
                'example_text'             => 'سَمَاء',
                'priority'                 => 31,
            ],
            [
                'name'                     => 'Madd Munfasil (Separated)',
                'slug'                     => 'madd-munfasil',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#E53935',
                'description'              => 'A 4 or 5 beat prolongation when a Madd letter ends a word and Hamzah begins the next. (2 beats is permissible in some readings).',
                'example_text'             => 'بِمَا أُنزِلَ',
                'priority'                 => 32,
            ],
            [
                'name'                     => 'Madd Badal (Substituted)',
                'slug'                     => 'madd-badal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'A 2-beat prolongation occurring when a Hamzah precedes a Madd letter.',
                'example_text'             => 'ءَامَنُوا',
                'priority'                 => 33,
            ],
            [
                'name'                     => 'Madd Aridh li-Sukun (Temporary Stop)',
                'slug'                     => 'madd-aridh',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#E53935',
                'description'              => 'Prolonging the Madd letter for 2, 4, or 6 beats when stopping on the last letter of a word, creating a temporary Sukoon.',
                'example_text'             => 'ٱلْعَالَمِينَ',
                'priority'                 => 34,
            ],
            [
                'name'                     => 'Madd Leen (Soft Madd)',
                'slug'                     => 'madd-leen',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'Prolonging a Waw or Yaa with a Sukoon (preceded by a Fatha) for 2, 4, or 6 beats when stopping on the word.',
                'example_text'             => 'قُرَيْشٍ',
                'priority'                 => 35,
            ],
            [
                'name'                     => 'Madd Lazim Kalimi Muthaqqal (Compulsory Word Heavy)',
                'slug'                     => 'madd-lazim-kalimi-muthaqqal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#B71C1C',
                'description'              => 'A strict 6-beat prolongation when a Madd letter is followed by a Shaddah in the same word.',
                'example_text'             => 'ٱلضَّآلِّينَ',
                'priority'                 => 36,
            ],
            [
                'name'                     => 'Madd Lazim Kalimi Mukhaffaf (Compulsory Word Light)',
                'slug'                     => 'madd-lazim-kalimi-mukhaffaf',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#B71C1C',
                'description'              => 'A strict 6-beat prolongation when a Madd letter is followed by a non-merged Sukoon in the same word.',
                'example_text'             => 'ءَآلْـَٔـٰنَ',
                'priority'                 => 37,
            ],
            [
                'name'                     => 'Madd Lazim Harfi Muthaqqal (Compulsory Letter Heavy)',
                'slug'                     => 'madd-lazim-harfi-muthaqqal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#B71C1C',
                'description'              => 'A strict 6-beat prolongation in the disconnected letters (Muqatta\'at) where one letter merges into the next.',
                'example_text'             => 'طسٓمٓ',
                'priority'                 => 38,
            ],
            [
                'name'                     => 'Madd Lazim Harfi Mukhaffaf (Compulsory Letter Light)',
                'slug'                     => 'madd-lazim-harfi-mukhaffaf',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#B71C1C',
                'description'              => 'A strict 6-beat prolongation in the disconnected letters (Muqatta\'at) where the letter does not merge into the next.',
                'example_text'             => 'صٓ',
                'priority'                 => 39,
            ],
            [
                'name'                     => 'Madd Silah Kubra (Connecting)',
                'slug'                     => 'madd-silah-kubra',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'Prolonging the pronoun Ha (ـه) followed by a Hamzah, making 4/5 beats (Kubra).',
                'example_text'             => 'مَالَهُۥٓ أَخْلَدَهُ',
                'priority'                 => 40,
            ],
            [
                'name'                     => 'Madd Silah Sughra (Connecting)',
                'slug'                     => 'madd-silah-sughra',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'Prolonging the pronoun Ha (ـه) for 2 beats (Sughra) when it is NOT followed by a Hamzah.',
                'example_text'             => 'إِنَّهُۥ كَانَ',
                'priority'                 => 41,
            ],
            [
                'name'                     => 'Madd Iwad (Compensation)',
                'slug'                     => 'madd-iwad',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'A 2-beat prolongation that substitutes a Fathatain (double Fatha) when stopping on a word, turning it into a spoken Alif.',
                'example_text'             => 'عَلِيمًا',
                'priority'                 => 42,
            ],
            [
                'name'                     => 'Madd Tamkeen (Empowerment)',
                'slug'                     => 'madd-tamkeen',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'               => '#FF9800',
                'description'              => 'A 2-beat prolongation occurring when two consecutive Yaa letters appear; the first has a Shaddah and Kasrah, and the second has a Sukoon.',
                'example_text'             => 'حُيِّيتُم',
                'priority'                 => 43,
            ],

            // ==========================================
            // 4. THE LETTER RAA (ر)
            // ==========================================
            [
                'name'                     => 'Raa Tafkhim (Heavy Raa)',
                'slug'                     => 'raa-tafkhim',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'               => '#3F51B5',
                'description'              => 'Pronouncing the Raa with a full, heavy mouth. Occurs when Raa has a Fatha/Damma, or a Sukoon preceded by Fatha/Damma.',
                'example_text'             => 'رَبَّنَا',
                'priority'                 => 50,
            ],
            [
                'name'                     => 'Raa Tarqiq (Light Raa)',
                'slug'                     => 'raa-tarqiq',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'               => '#03A9F4',
                'description'              => 'Pronouncing the Raa with an empty, flat mouth. Occurs when Raa has a Kasra, or a Sukoon preceded by a Kasra.',
                'example_text'             => 'رِجَالٌ',
                'priority'                 => 51,
            ],
            [
                'name'                     => 'Raa Jawaz (Permissible Both Ways)',
                'slug'                     => 'raa-jawaz',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'               => '#9C27B0',
                'description'              => 'Rare scenarios where the Raa can be read as either heavy or light (e.g., when followed by a heavy letter with a Kasra, or stopping on specific words).',
                'example_text'             => 'فِرْقٍ',
                'priority'                 => 52,
            ],

            // ==========================================
            // 5. QALQALAH
            // ==========================================
            [
                'name'                     => 'Qalqalah Kubra (Major Echo)',
                'slug'                     => 'qalqalah-kubra',
                'tajweed_rule_category_id' => $cat('qalqalah'),
                'color_code'               => '#00BCD4',
                'description'              => 'A strong echoing bounce sound made when stopping on one of the Qalqalah letters (ق, ط, ب, ج, د) at the end of a word.',
                'example_text'             => 'ٱلْفَلَقِ',
                'priority'                 => 60,
            ],
            [
                'name'                     => 'Qalqalah Sughra (Minor Echo)',
                'slug'                     => 'qalqalah-sughra',
                'tajweed_rule_category_id' => $cat('qalqalah'),
                'color_code'               => '#00BCD4',
                'description'              => 'A softer echoing bounce sound made when a Qalqalah letter has a Sukoon in the middle of a word.',
                'example_text'             => 'يَجْعَلُ',
                'priority'                 => 61,
            ],

            // ==========================================
            // 6. GHUNNAH
            // ==========================================
            [
                'name'                     => 'Ghunnah Mushaddadah',
                'slug'                     => 'ghunnah-mushaddadah',
                'tajweed_rule_category_id' => $cat('ghunnah'),
                'color_code'               => '#4CAF50',
                'description'              => 'A mandatory 2-beat strong nasal sound whenever a Noon (ن) or Meem (م) carries a Shaddah.',
                'example_text'             => 'إِنَّ',
                'priority'                 => 62,
            ],

            // ==========================================
            // 7. LAFDH AL-JALALAH (The Word "Allah")
            // ==========================================
            [
                'name'                     => 'Laam Tafkhim (Heavy Laam)',
                'slug'                     => 'laam-tafkhim',
                'tajweed_rule_category_id' => $cat('laam-of-allah'),
                'color_code'               => '#3F51B5',
                'description'              => 'Pronouncing the Laam in the word "Allah" with a heavy, full mouth when preceded by a Fatha or Damma.',
                'example_text'             => 'شَهِدَ اللَّهُ',
                'priority'                 => 70,
            ],
            [
                'name'                     => 'Laam Tarqiq (Light Laam)',
                'slug'                     => 'laam-tarqiq',
                'tajweed_rule_category_id' => $cat('laam-of-allah'),
                'color_code'               => '#03A9F4',
                'description'              => 'Pronouncing the Laam in the word "Allah" with a light, empty mouth when preceded by a Kasra.',
                'example_text'             => 'بِسْمِ اللَّهِ',
                'priority'                 => 71,
            ],

            // ==========================================
            // 8. AL-LAAM AL-TA'REEF (The Definite Article)
            // ==========================================
            [
                'name'                     => 'Idhhar Qamari (Clear Laam)',
                'slug'                     => 'idhhar-qamari',
                'tajweed_rule_category_id' => $cat('laam-al-taareef'),
                'color_code'               => '#000000',
                'description'              => 'Pronouncing the Laam clearly when followed by any of the 14 lunar letters (e.g., ب، ج، ح).',
                'example_text'             => 'ٱلْقَمَرِ',
                'priority'                 => 80,
            ],
            [
                'name'                     => 'Idgham Shamsi (Merged Laam)',
                'slug'                     => 'idgham-shamsi',
                'tajweed_rule_category_id' => $cat('laam-al-taareef'),
                'color_code'               => '#9E9E9E',
                'description'              => 'Merging the Laam completely into the following letter without pronouncing it, applied to the 14 solar letters (e.g., ش، س، ت).',
                'example_text'             => 'ٱلشَّمْسِ',
                'priority'                 => 81,
            ],

            // ==========================================
            // 9. ADVANCED IDGHAM (Letter Merging)
            // ==========================================
            [
                'name'                     => 'Idgham Mutamathilayn (Identical)',
                'slug'                     => 'idgham-mutamathilayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'               => '#9E9E9E',
                'description'              => 'Merging two identical letters when the first has a Sukoon and the second has a vowel.',
                'example_text'             => 'ٱضْرِب بِّعَصَاكَ',
                'priority'                 => 90,
            ],
            [
                'name'                     => 'Idgham Mutajanisayn (Similar)',
                'slug'                     => 'idgham-mutajanisayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'               => '#9E9E9E',
                'description'              => 'Merging two letters that share the same articulation point but have different characteristics (e.g., Ta and Da).',
                'example_text'             => 'أُجِيبَت دَّعْوَتُكُمَا',
                'priority'                 => 91,
            ],
            [
                'name'                     => 'Idgham Mutaqaribayn (Close)',
                'slug'                     => 'idgham-mutaqaribayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'               => '#9E9E9E',
                'description'              => 'Merging two letters that are very close in articulation point and characteristics (e.g., Qaf and Kaf).',
                'example_text'             => 'أَلَمْ نَخْلُقكُّم',
                'priority'                 => 92,
            ],

            // ==========================================
            // 10. SPECIAL RULES
            // ==========================================
            [
                'name'                     => 'Saktah (Breathless Pause)',
                'slug'                     => 'saktah',
                'tajweed_rule_category_id' => $cat('special-rules'),
                'color_code'               => '#E91E63',
                'description'              => 'A mandatory short pause of about 2 beats without breaking the breath. Marked by a small (س).',
                'example_text'             => 'عِوَجَا ۜ قَيِّمًا',
                'priority'                 => 100,
            ],
        ];

        // ─────────────────────────────────────────────────────────────────
        // Add timestamps and is_active to every row then batch insert
        // ─────────────────────────────────────────────────────────────────
        foreach ($rules as $rule) {
            $rule['is_active'] = true;
            \App\Models\TajweedRule::updateOrCreate(
                ['slug' => $rule['slug']],
                $rule
            );
        }
    }
}