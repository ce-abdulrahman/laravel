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

        $rules = [
            // ==========================================
            // 1. NOON SAKINAH & TANWEEN
            // ==========================================
            [
                'name_en'        => 'Idhhar Halqi (Clear)',
                'name_ku'        => 'ئیزھاری حەلقی',
                'name_ar'        => 'الإظهار الحلقي',
                'slug'           => 'idhhar-halqi',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'     => '#a54e4eff',
                'description_en' => 'Pronouncing the Noon Sakinah or Tanween clearly without extra Ghunnah when followed by throat letters (ء, ه, ع, ح, غ, خ).',
                'description_ku' => 'دەرخستنی نوونی ساکن یان تەنووین بەبێ غوننە لە کاتی گەیشتن بە یەکێک لە پیتی قورگەکان (ء، هـ، ع، ح، غ، خ).',
                'description_ar' => 'إخراج النون الساكنة أو التنوين من مخرجها من غير غنة زائدة إذا جاء بعدها أحد حروف الحلق.',
                'example_text'   => 'مِنْ خَوْفٍ',
                'priority'       => 10,
            ],
            [
                'name_en'        => 'Idgham Halqi',
                'name_ku'        => 'ئیدغامی حەلقی',
                'name_ar'        => 'الإدغام الحلقي',
                'slug'           => 'idgham-halqi',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'     => '#4CAF50',
                'description_en' => 'Merging the Noon into the following letter with a 2-beat nasal hold. Letters: ي, ن, م, و, ل, ر.',
                'description_ku' => 'تێکەڵکردنی نوونی ساکن یان تەنووین لەگەڵ پیتی دوای خۆی بە غوننەوە بۆ ماوەی ٢ جووڵە (ي، ن، م، و، ل، ر).',
                'description_ar' => 'دمج النون الساكنة أو التنوين في الحرف الذي يليها مع صوت الغنة بمقدار حركتين (ي، ن، م، و، ل، ر).',
                'example_text'   => 'مَن يَعْمَلْ',
                'priority'       => 11,
            ],
            [
                'name_en'        => 'Iqlab (Changing)',
                'name_ku'        => 'ئیقلاب (گۆڕین)',
                'name_ar'        => 'الإقلاب',
                'slug'           => 'iqlab',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'     => '#2196F3',
                'description_en' => 'Changing the Noon sound into a hidden Meem with a 2-beat Ghunnah when followed by Ba (ب).',
                'description_ku' => 'گۆڕینی دەنگی نوونی ساکن یان تەنووین بۆ میمێکی شاراوە لەگەڵ دروستبوونی غوننە کاتێک پیتەکە بە (ب) دەگات.',
                'description_ar' => 'قلب النون الساكنة أو التنوين ميماً مخفاة مع الغنة عند التقائها بحرف الباء.',
                'example_text'   => 'مِن بَعْدِ',
                'priority'       => 12,
            ],
            [
                'name_en'        => 'Ikhfa Haqiqi (Hiding)',
                'name_ku'        => 'ئیخفای حەقیقی',
                'name_ar'        => 'الإخفاء الحقيقي',
                'slug'           => 'ikhfa-haqiqi',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'     => '#4CAF50',
                'description_en' => 'Hiding the Noon sound in the nasal cavity with a 2-beat hold before the remaining 15 letters.',
                'description_ku' => 'شاردنەوەی دەنگی نوونی ساکن یان تەنووین لەگەڵ غوننە بۆ ماوەی ٢ جووڵە لەگەڵ ١٥ پیتەکەی تر.',
                'description_ar' => 'نطق النون الساكنة أو التنوين بصفة بين الإظهار والإدغام مع بقاء الغنة بمقدار حركتين عند التقائها ببقية الحروف الـ 15.',
                'example_text'   => 'مِن قَبْلُ',
                'priority'       => 13,
            ],
            [
                'name_en'        => 'Words with Idgham',
                'name_ku'        => 'وشە شازەکانی نوون و تەنوین',
                'name_ar'        => 'الكلمات التي فيها إدغام',
                'slug'           => 'words-with-idgham',
                'tajweed_rule_category_id' => $cat('noon-sakinah-tanween'),
                'color_code'     => '#000000',
                'description_en' => 'Exceptional cases where Noon and an Idgham letter meet in one word; the Noon is pronounced clearly without merging.',
                'description_ku' => 'کاتێک نوونی ساکن و پیتی ئیدغام لە یەک وشەدا کۆدەبنەوە و ناتواندرێنەوە بەڵکو بە ڕوونی دەخوێندرێنەوە.',
                'description_ar' => 'نطق النون الساكنة بوضوح دون إدغام عند التقائها بحروف الإدغام في كلمة واحدة منعاً للالتباس.',
                'example_text'   => 'دنيا, صِنْوَان, قِنْوَان, بُنْيَان , عُنْوَان',
                'priority'       => 14,
            ],

            // ==========================================
            // 2. MEEM SAKINAH
            // ==========================================
            [
                'name_en'        => 'Ikhfa Shafawi', 
                'name_ku'        => 'ئیخفای دەمی',
                'name_ar'        => 'الإخفاء الشفوي',
                'slug'           => 'ikhfa-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'     => '#4CAF50',
                'description_en' => 'Hiding the Meem lightly with a 2-beat Ghunnah when followed by Ba (ب).',
                'description_ku' => 'شاردنەوەی میمی ساکن لەگەڵ غوننە کاتێک پیتی (ب) لە دوایەوە دێت.',
                'description_ar' => 'إخفاء الميم الساكنة مع الغنة بمقدار حركتين إذا وقع بعدها حرف الباء.',
                'example_text'   => 'تَرْمِيهِم بِحِجَارَةٍ',
                'priority'       => 20,
            ],
            [
                'name_en'        => 'Idgham Shafawi',
                'name_ku'        => 'ئیدغامی دەمی',
                'name_ar'        => 'الإدغام الشفوي',
                'slug'           => 'idgham-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'     => '#4CAF50',
                'description_en' => 'Merging the Meem into another Meem with a 2-beat Ghunnah.',
                'description_ku' => 'تێکەڵکردنی میمی ساکن لەگەڵ میمێکی تری جووڵاو لەگەڵ غوننە بۆ ماوەی ٢ جووڵە.',
                'description_ar' => 'إدغام الميم الساكنة في ميم متحركة تليها مع الغنة بمقدار حركتين.',
                'example_text'   => 'لَهُم مَّثَلًا',
                'priority'       => 21,
            ],
            [
                'name_en'        => 'Idhhar Shafawi',
                'name_ku'        => 'ئیزھاری دەمی',
                'name_ar'        => 'الإظهار الشفوي',
                'slug'           => 'idhhar-shafawi',
                'tajweed_rule_category_id' => $cat('meem-sakinah'),
                'color_code'     => '#000000',
                'description_en' => 'Pronouncing the Meem clearly with closed lips before any letter other than Meem or Ba.',
                'description_ku' => 'دەرخستنی میمی ساکن بە ڕوونی لە کاتی گەیشتن بە هەموو پیتەکان بیجگە لە (ب) و (م).',
                'description_ar' => 'إظهار الميم الساكنة بوضوح عند التقائها بجميع الحروف عدا الباء والميم.',
                'example_text'   => 'أَلَمْ تَرَ',
                'priority'       => 22,
            ],

            // ==========================================
            // 3. MADD (PROLONGATION)
            // ==========================================
            [
                'name_en'        => 'Madd Tabii (Natural Madd)',
                'name_ku'        => 'مەدی سروشتی',
                'name_ar'        => 'المد الطبيعي',
                'slug'           => 'madd-tabii',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'The natural 2-beat prolongation of Alif, Waw, or Yaa when not followed by a Hamzah or Sukoon.',
                'description_ku' => 'درێژکردنەوەی دەنگی پیتەکانی مەد (ا، و، ی) بە بڕی ٢ جووڵە کاتێک هەمزە یان ساکنی لە دوا نەبێت.',
                'description_ar' => 'إطالة الصوت بحرف المد (الألف، الواو، الياء) بمقدار حركتين لعدم وجود همز أو سكون بعده.',
                'example_text'   => 'نُوحِيهَا',
                'priority'       => 30,
            ],
            [
                'name_en'        => 'Madd Muttasil (Attached)',
                'name_ku'        => 'مەدی متصل',
                'name_ar'        => 'المد المتصل',
                'slug'           => 'madd-muttasil',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#E53935',
                'description_en' => 'A mandatory 4 or 5 beat prolongation when a Madd letter is followed by a Hamzah in the same word.',
                'description_ku' => 'کاتێک پیتی مەد و هەمزە لە یەک وشەدا کۆدەبنەوە، بە بڕی ٤ بۆ ٥ جووڵە درێژ دەکرێتەوە.',
                'description_ar' => 'أن يأتي حرف المد وبعده الهمزة في كلمة واحدة، ويمد بمقدار 4 أو 5 حركات.',
                'example_text'   => 'سَمَاء',
                'priority'       => 31,
            ],
            [
                'name_en'        => 'Madd Munfasil (Separated)',
                'name_ku'        => 'مەدی منفصل',
                'name_ar'        => 'المد المنفصل',
                'slug'           => 'madd-munfasil',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#E53935',
                'description_en' => 'A 4 or 5 beat prolongation when a Madd letter ends a word and Hamzah begins the next. (2 beats is permissible in some readings).',
                'description_ku' => 'کاتێک پیتی مەد لە کۆتایی وشەیەکدا بێت و هەمزە لە سەرەتای وشەی دواتر بێت، بە بڕی ٤ بۆ ٥ جووڵە درێژ دەکرێتەوە.',
                'description_ar' => 'أن يأتي حرف المد في آخر كلمة والهمزة في أول الكلمة التي تليها، ويمد بمقدار 4 أو 5 حركات.',
                'example_text'   => 'بِمَا أُنزِلَ',
                'priority'       => 32,
            ],
            [
                'name_en'        => 'Madd Badal (Substituted)',
                'name_ku'        => 'مەدی بەدەل',
                'name_ar'        => 'مد البدل',
                'slug'           => 'madd-badal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'A 2-beat prolongation occurring when a Hamzah precedes a Madd letter.',
                'description_ku' => 'پێشکەوتنی هەمزە بەسەر پیتی مەددا لە یەک وشەدا، بە بڕی ٢ جووڵە درێژ دەکرێتەوە.',
                'description_ar' => 'أن تتقدم الهمزة على حرف المد في كلمة واحدة، ويمد بمقدار حركتين.',
                'example_text'   => 'ءَامَنُوا',
                'priority'       => 33,
            ],
            [
                'name_en'        => 'Madd Aridh li-Sukun (Temporary Stop)',
                'name_ku'        => 'مەدی عارز بۆ ساکن',
                'name_ar'        => 'المد العارض للسكون',
                'slug'           => 'madd-aridh',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#E53935',
                'description_en' => 'Prolonging the Madd letter for 2, 4, or 6 beats when stopping on the last letter of a word, creating a temporary Sukoon.',
                'description_ku' => 'درێژکردنەوەی پیتی مەد بە بڕی ٢، ٤، یان ٦ جووڵە کاتێک بەهۆی وەستانەوە پیتەکەی تەنیشتی ساکن دەبێت.',
                'description_ar' => 'أن يأتي بعد حرف المد حرف متحرك يتم تسكينه مؤقتاً بسبب الوقف، ويمد بمقدار 2 أو 4 أو 6 حركات.',
                'example_text'   => 'ٱلْعَالَمِينَ',
                'priority'       => 34,
            ],
            [
                'name_en'        => 'Madd Leen (Soft Madd)',
                'name_ku'        => 'مەدی لین',
                'name_ar'        => 'مد اللين',
                'slug'           => 'madd-leen',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'Prolonging a Waw or Yaa with a Sukoon (preceded by a Fatha) for 2, 4, or 6 beats when stopping on the word.',
                'description_ku' => 'درێژکردنەوەی پیتی لینی ساکن (و، ی پێش فەتحە) بۆ ماوەی ٢، ٤، یان ٦ جووڵە کاتێک وەستان لەسەر وشەکە دەکرێت.',
                'description_ar' => 'إطالة الصوت في الواو أو الياء الساكنتين المفتوح ما قبلهما عند الوقف بمقدار 2 أو 4 أو 6 حركات.',
                'example_text'   => 'قُرَيْشٍ',
                'priority'       => 35,
            ],
            [
                'name_en'        => 'Madd Lazim Kalimi Muthaqqal (Compulsory Word Heavy)',
                'name_ku'        => 'مەدی لازمی کەلیمی قورس',
                'name_ar'        => 'المد اللازم الكلمي المثقل',
                'slug'           => 'madd-lazim-kalimi-muthaqqal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#B71C1C',
                'description_en' => 'A strict 6-beat prolongation when a Madd letter is followed by a Shaddah in the same word.',
                'description_ku' => 'درێژکردنەوەی پیتی مەد بە بڕی ٦ جووڵە بەهۆی هاتنی پیتێکی شەددەدار لە دوایەوە لە یەک وشەدا.',
                'description_ar' => 'أن يأتي بعد حرف المد حرف مشدد في كلمة واحدة، ويمد بمقدار 6 حركات وجوباً.',
                'example_text'   => 'ٱلضَّآلِّينَ',
                'priority'       => 36,
            ],
            [
                'name_en'        => 'Madd Lazim Kalimi Mukhaffaf (Compulsory Word Light)',
                'name_ku'        => 'مەدی لازمی کەلیمی سووک',
                'name_ar'        => 'المد اللازم الكلمي المخفف',
                'slug'           => 'madd-lazim-kalimi-mukhaffaf',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#B71C1C',
                'description_en' => 'A strict 6-beat prolongation when a Madd letter is followed by a non-merged Sukoon in the same word.',
                'description_ku' => 'درێژکردنەوەی پیتی مەد بە بڕی ٦ جووڵە بەهۆی هاتنی پیتێکی ساکنی ڕەسەن بەبێ تێکەڵبوون لە یەک وشەدا.',
                'description_ar' => 'أن يأتي بعد حرف المد حرف ساكن سكوناً أصلياً غير مدغم في كلمة واحدة، ويمد بمقدار 6 حركات وجوباً.',
                'example_text'   => 'ءَآلْـَٔـٰنَ',
                'priority'       => 37,
            ],
            [
                'name_en'        => 'Madd Lazim Harfi Muthaqqal (Compulsory Letter Heavy)',
                'name_ku'        => 'مەدی لازمی حەرفی قورس',
                'name_ar'        => 'المد اللازم الحرفي المثقل',
                'slug'           => 'madd-lazim-harfi-muthaqqal',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#B71C1C',
                'description_en' => 'A strict 6-beat prolongation in the disconnected letters (Muqatta\'at) where one letter merges into the next.',
                'description_ku' => 'درێژکردنەوەی پیتی مەد بە بڕی ٦ جووڵە لە پیتە بچڕاوەکانی سەرەتای سورەتەکاندا کە پیتەکە تێکەڵ بە دوای خۆی دەبێت.',
                'description_ar' => 'أن يأتي حرف المد في حروف فواتح السور ويدغم الحرف الذي بعده، ويمد بمقدار 6 حركات وجوباً.',
                'example_text'   => 'طسٓمٓ',
                'priority'       => 38,
            ],
            [
                'name_en'        => 'Madd Lazim Harfi Mukhaffaf (Compulsory Letter Light)',
                'name_ku'        => 'مەدی لازمی حەرفی سووک',
                'name_ar'        => 'المد اللازم الحرفي المخفف',
                'slug'           => 'madd-lazim-harfi-mukhaffaf',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#B71C1C',
                'description_en' => 'A strict 6-beat prolongation in the disconnected letters (Muqatta\'at) where the letter does not merge into the next.',
                'description_ku' => 'درێژکردنەوەی پیتی مەد بە بڕی ٦ جووڵە لە پیتە بچڕاوەکانی سەرەتای سورەتەکاندا بەبێ تێکەڵبوون بە پیتی دواتر.',
                'description_ar' => 'أن يأتي حرف المد في حروف فواتح السور ولا يدغم الحرف الذي بعده، ويمد بمقدار 6 حركات وجوباً.',
                'example_text'   => 'صٓ',
                'priority'       => 39,
            ],
            [
                'name_en'        => 'Madd Silah Kubra (Connecting)',
                'name_ku'        => 'مەدی سڵەی گەورە',
                'name_ar'        => 'مد الصلة الكبرى',
                'slug'           => 'madd-silah-kubra',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'Prolonging the pronoun Ha (ـه) followed by a Hamzah, making 4/5 beats (Kubra).',
                'description_ku' => 'درێژکردنەوەی ڕاناوی (هـ) بە بڕی ٤ یان ٥ جووڵە کاتێک دەکەوێتە نێوان پیتێکی جووڵاو و هەمزەوە.',
                'description_ar' => 'إطالة ضمة أو كسرة هاء الضمير الواقعة بين متحركين عندما يكون المتحرك الثاني همزة بمقدار 4 أو 5 حركات.',
                'example_text'   => 'مَالَهُۥٓ أَخْلَدَهُ',
                'priority'       => 40,
            ],
            [
                'name_en'        => 'Madd Silah Sughra (Connecting)',
                'name_ku'        => 'مەدی سڵەی بچووک',
                'name_ar'        => 'مد الصلة الصغرى',
                'slug'           => 'madd-silah-sughra',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'Prolonging the pronoun Ha (ـه) for 2 beats (Sughra) when it is NOT followed by a Hamzah.',
                'description_ku' => 'درێژکردنەوەی ڕاناوی (هـ) بە بڕی ٢ جووڵە کاتێک دەکەوێتە نێوان دوو پیتی جووڵاو بەبێ هاتنی هەمزە لە دوایەوە.',
                'description_ar' => 'إطالة ضمة أو كسرة هاء الضمير الواقعة بين متحركين عندما لا يكون المتحرك الثاني همزة بمقدار حركتين.',
                'example_text'   => 'إِنَّهُۥ كَانَ',
                'priority'       => 41,
            ],
            [
                'name_en'        => 'Madd Iwad (Compensation)',
                'name_ku'        => 'مەدی عەواز',
                'name_ar'        => 'مد العوض',
                'slug'           => 'madd-iwad',
                'tajweed_rule_category_id' => $cat('madd-prolongation'),
                'color_code'     => '#FF9800',
                'description_en' => 'A 2-beat prolongation that substitutes a Fathatain (double Fatha) when stopping on a word, turning it into a spoken Alif.',
                'description_ku' => 'گۆڕینی تەنوینی فەتحە (ـً) بۆ ئەلف بە بڕی ٢ جووڵە تەنها لە کاتی وەستان لەسەر وشەکەدا.',
                'description_ar' => 'التعويض عن تنوين الفتح بألف تمد بمقدار حركتين عند الوقف على الكلمة.',
                'example_text'   => 'عَلِيمًا',
                'priority'       => 42,
            ],

            // ==========================================
            // 4. THE LETTER RAA (ر)
            // ==========================================
            [
                'name_en'        => 'Raa Tafkhim (Heavy Raa)',
                'name_ku'        => 'قەڵەوکردنی پیتی ڕاء',
                'name_ar'        => 'تفخيم الراء',
                'slug'           => 'raa-tafkhim',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'     => '#3F51B5', 
                'description_en' => 'Pronouncing the Raa with a full, heavy mouth. Occurs when Raa has a Fatha/Damma, or a Sukoon preceded by Fatha/Damma.',
                'description_ku' => 'خوێندنەوەی پیتی ڕاء بە قەڵەوی (تەفخیم) بەهۆی هەبوونی فەتحە، زەممە یان ساکن پێش فەتحە و زەممە.',
                'description_ar' => 'نطق حرف الراء بصوت غليظ وممتلئ الفم عند فتحه أو ضمه أو سكونه بشروط محددة.',
                'example_text'   => 'رَبَّنَا',
                'priority'       => 50,
            ],
            [
                'name_en'        => 'Raa Tarqiq (Light Raa)',
                'name_ku'        => 'تەنککردنی پیتی ڕاء',
                'name_ar'        => 'ترقيق الراء',
                'slug'           => 'raa-tarqiq',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'     => '#03A9F4',
                'description_en' => 'Pronouncing the Raa with an empty, flat mouth. Occurs when Raa has a Kasra, or a Sukoon preceded by a Kasra.',
                'description_ku' => 'خوێندنەوەی پیتی ڕاء بە تەنکی (تەرقێق) بەهۆی هەبوونی کەسرە یان ساکن پێش کەسرە.',
                'description_ar' => 'نطق حرف الراء بصوت نحيف عند كسره أو سكونه بشروط محددة.',
                'example_text'   => 'رِجَالٌ',
                'priority'       => 51,
            ],
            [
                'name_en'        => 'Raa Jawaz (Permissible Both Ways)',
                'name_ku'        => 'دروستبوونی هەردوو بار لە ڕاء',
                'name_ar'        => 'جواز الوجهين في الراء',
                'slug'           => 'raa-jawaz',
                'tajweed_rule_category_id' => $cat('rules-of-raa'),
                'color_code'     => '#9C27B0',
                'description_en' => 'Rare scenarios where the Raa can be read as either heavy or light (e.g., when followed by a heavy letter with a Kasra, or stopping on specific words).',
                'description_ku' => 'ئەو حاڵەتانەی کە تێیدا خوێندنەوەی ڕاء بە قەڵەوی یان تەنکی بە دروستی ڕێگەپێدراوە.',
                'description_ar' => 'جواز نطق الراء بالتفخيم أو الترقيق في بعض الحالات الخاصة.',
                'example_text'   => 'فِرْقٍ',
                'priority'       => 52,
            ],

            // ==========================================
            // 5. QALQALAH
            // ==========================================
            [
                'name_en'        => 'Qalqalah Kubra (Major Echo)',
                'name_ku'        => 'قەلقەلەی گەورە',
                'name_ar'        => 'القلقلة الكبرى',
                'slug'           => 'qalqalah-kubra',
                'tajweed_rule_category_id' => $cat('qalqalah'),
                'color_code'     => '#00BCD4',
                'description_en' => 'A strong echoing bounce sound made when stopping on one of the Qalqalah letters (ق, ط, ب, ج, د) at the end of a word.',
                'description_ku' => 'لەرزاندنی بەهێزی دەنگ لە کاتی وەستان لەسەر پیتەکانی قەلقەلە (ق، ط، ب، ج، د) لە کۆتایی وشەدا.',
                'description_ar' => 'اضطراب قوي في مخرج الحرف عند النطق به ساكناً بسبب الوقف في نهاية الكلمة.',
                'example_text'   => 'ٱلْفَلَقِ',
                'priority'       => 60,
            ],
            [
                'name_en'        => 'Qalqalah Sughra (Minor Echo)',
                'name_ku'        => 'قەلقەلەی بچووک',
                'name_ar'        => 'القلقلة الصغرى',
                'slug'           => 'qalqalah-sughra',
                'tajweed_rule_category_id' => $cat('qalqalah'),
                'color_code'     => '#00BCD4',
                'description_en' => 'A softer echoing bounce sound made when a Qalqalah letter has a Sukoon in the middle of a word.',
                'description_ku' => 'لەرزاندنی مامناوەندی دەنگ لە کاتی هاتنی پیتەکانی قەلقەلە بە ساکنی لە ناوەڕاستی وشەدا.',
                'description_ar' => 'اضطراب متوسط في مخرج الحرف عند النطق به ساكناً في وسط الكلمة.',
                'example_text'   => 'يَجْعَلُ',
                'priority'       => 61,
            ],

            // ==========================================
            // 6. GHUNNAH
            // ==========================================
            [
                'name_en'        => 'Ghunnah Mushaddadah',
                'name_ku'        => 'غوننەی شەددەدار',
                'name_ar'        => 'الغنة المشددة',
                'slug'           => 'ghunnah-mushaddadah',
                'tajweed_rule_category_id' => $cat('ghunnah'),
                'color_code'     => '#4CAF50',
                'description_en' => 'A mandatory 2-beat strong nasal sound whenever a Noon (ن) or Meem (م) carries a Shaddah.',
                'description_ku' => 'دەرکردنی دەنگی غوننە لە لوتەوە بۆ ماوەی ٢ جووڵە کاتێک پیتەکانی نوون (ن) یان میم (م) شەددەیان لەسەر بێت.',
                'description_ar' => 'إخراج صوت الغنة من الخيشوم بمقدار حركتين وجوباً عند نطق النون أو الميم المشددتين.',
                'example_text'   => 'إِنَّ',
                'priority'       => 62,
            ],

            // ==========================================
            // 7. LAFDH AL-JALALAH (The Word "Allah")
            // ==========================================
            [
                'name_en'        => 'Laam Tafkhim (Heavy Laam)',
                'name_ku'        => 'قەڵەوکردنی لامی جەلالە',
                'name_ar'        => 'تفخيم اللام في لفظ الجلالة',
                'slug'           => 'laam-tafkhim',
                'tajweed_rule_category_id' => $cat('laam-of-allah'),
                'color_code'     => '#3F51B5',
                'description_en' => 'Pronouncing the Laam in the word "Allah" with a heavy, full mouth when preceded by a Fatha or Damma.',
                'description_ku' => 'خوێندنەوەی پیتی لامی لفظ الجلالة (الله) بە قەڵەوی ئەگەر پێشینەکەی فەتحە یان زەممە بێت.',
                'description_ar' => 'تفخيم صوت اللام في اسم الجلالة (الله) إذا سبقه فتح أو ضم.',
                'example_text'   => 'شَهِدَ اللَّهُ',
                'priority'       => 70,
            ],
            [
                'name_en'        => 'Laam Tarqiq (Light Laam)',
                'name_ku'        => 'تەنککردنی لامی جەلالە',
                'name_ar'        => 'ترقيق اللام في لفظ الجلالة',
                'slug'           => 'laam-tarqiq',
                'tajweed_rule_category_id' => $cat('laam-of-allah'),
                'color_code'     => '#03A9F4',
                'description_en' => 'Pronouncing the Laam in the word "Allah" with a light, empty mouth when preceded by a Kasra.',
                'description_ku' => 'خوێندنەوەی پیتی لامی لفظ الجلالة (الله) بە تەنکی ئەگەر پێشینەکەی کەسرە بێت.',
                'description_ar' => 'ترقيق صوت اللام في اسم الجلالة (الله) إذا سبقه كسر.',
                'example_text'   => 'بِسْمِ اللَّهِ',
                'priority'       => 71,
            ],

            // ==========================================
            // 8. AL-LAAM AL-TA'REEF (The Definite Article)
            // ==========================================
            [
                'name_en'        => 'Idhhar Qamari (Clear Laam)',
                'name_ku'        => 'ئیزھاری قەمەری',
                'name_ar'        => 'الإظهار القمري',
                'slug'           => 'idhhar-qamari',
                'tajweed_rule_category_id' => $cat('laam-al-taareef'),
                'color_code'     => '#000000',
                'description_en' => 'Pronouncing the Laam clearly when followed by any of the 14 lunar letters (e.g., ب، ج، ح).',
                'description_ku' => 'خوێندنەوەی لامی پێناس بە ڕوونی کاتێک بەدوایدا یەکێک لە ١٤ پیتی قەمەری بێت.',
                'description_ar' => 'نطق اللام الساكنة في أل التعريف بوضوح إذا وقع بعدها أحد الحروف القمرية الـ 14.',
                'example_text'   => 'ٱلْقَمَرِ',
                'priority'       => 80,
            ],
            [
                'name_en'        => 'Idgham Shamsi (Merged Laam)',
                'name_ku'        => 'ئیدغامی شەمسى',
                'name_ar'        => 'الإدغام الشمسي',
                'slug'           => 'idgham-shamsi',
                'tajweed_rule_category_id' => $cat('laam-al-taareef'),
                'color_code'     => '#9E9E9E',
                'description_en' => 'Merging the Laam completely into the following letter without pronouncing it, applied to the 14 solar letters (e.g., ش، س، ت).',
                'description_ku' => 'تواندنەوەی لامی پێناس لە ناو پیتی دوای خۆی بەبێ خوێندنەوە کاتێک بەدوایدا یەکێک لە ١٤ پیتی شەمسى بێت.',
                'description_ar' => 'دمج اللام الساكنة في أل التعريف بالحرف الذي يليها إذا وقع بعدها أحد الحروف الشمسية الـ 14.',
                'example_text'   => 'ٱلشَّمْسِ',
                'priority'       => 81,
            ],

            // ==========================================
            // 9. ADVANCED IDGHAM (Letter Merging)
            // ==========================================
            [
                'name_en'        => 'Idgham Mutamathilayn (Identical)',
                'name_ku'        => 'ئیدغامی هاوشێوەکان',
                'name_ar'        => 'إدغام المتماثلين',
                'slug'           => 'idgham-mutamathilayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'     => '#9E9E9E',
                'description_en' => 'Merging two identical letters when the first has a Sukoon and the second has a vowel.',
                'description_ku' => 'تێکەڵکردنی دوو پیتی هاوشێوە کە یەکەم ساکن و دووەم جووڵاو بێت لە یەک وشە یان دوو وشەدا.',
                'description_ar' => 'إدغام حرفين متفقين مخرجاً وصفة بحيث يكون الأول ساكناً والثاني متحركاً.',
                'example_text'   => 'ٱضْرِب بِّعَصَاكَ',
                'priority'       => 90,
            ],
            [
                'name_en'        => 'Idgham Mutajanisayn (Similar)',
                'name_ku'        => 'ئیدغامی هاوڕەگەزەکان',
                'name_ar'        => 'إدغام المتجانسين',
                'slug'           => 'idgham-mutajanisayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'     => '#9E9E9E',
                'description_en' => 'Merging two letters that share the same articulation point but have different characteristics (e.g., Ta and Da).',
                'description_ku' => 'تێکەڵکردنی دوو پیت کە یەک دەرچەیان هەیە بەڵام سیفەتیان جیاوازە کاتێک یەکەم ساکن و دووەم جووڵاو بێت.',
                'description_ar' => 'إدغام حرفين اتفقا في المخرج واختلفا في الصفات، بحيث يكون الأول ساكناً والثاني متحركاً.',
                'example_text'   => 'أُجِيبَت دَّعْوَتُكُمَا',
                'priority'       => 91,
            ],
            [
                'name_en'        => 'Idgham Mutaqaribayn (Close)',
                'name_ku'        => 'ئیدغامی لێک نزیکەکان',
                'name_ar'        => 'إدغام المتقاربين',
                'slug'           => 'idgham-mutaqaribayn',
                'tajweed_rule_category_id' => $cat('advanced-idgham'),
                'color_code'     => '#9E9E9E',
                'description_en' => 'Merging two letters that are very close in articulation point and characteristics (e.g., Qaf and Kaf).',
                'description_ku' => 'تێکەڵکردنی دوو پیت کە لە دەرچە یان سیفەتدا لێک نزیک بن کاتێک یەکەم ساکن و دووەم جووڵاو بێت.',
                'description_ar' => 'إدغام حرفين تقاربا في المخرج والصفة أو أحدهما، بحيث يكون الأول ساكناً والثاني متحركاً.',
                'example_text'   => 'أَلَمْ نَخْلُقكُّم',
                'priority'       => 92,
            ],

            // ==========================================
            // 10. SPECIAL RULES
            // ==========================================
            [
                'name_en'        => 'Saktah (Breathless Pause)',
                'name_ku'        => 'سەکتە (وەستانی بێ هەناسە)',
                'name_ar'        => 'السكت',
                'slug'           => 'saktah',
                'tajweed_rule_category_id' => $cat('special-rules'),
                'color_code'     => '#E91E63',
                'description_en' => 'A mandatory short pause of about 2 beats without breaking the breath. Marked by a small (س).',
                'description_ku' => 'وەستانێکی زۆر کورت لە کاتی خوێندنەوەدا بەبێ کێشانی هەناسە بە مەبەستی ڕوونکندرەوەی مانا.',
                'description_ar' => 'قطع الصوت زمناً يسيراً دون تنفس بنية استئناف القراءة.',
                'example_text'   => 'عِوَجَا ۜ قَيِّمًا',
                'priority'       => 100,
            ],
            [
                'name_en'        => 'Silent Letters (Tuktab wa la Tantaq)',
                'name_ku'        => 'پیتە بێدەنگەکان (دەنووسرێن و ناخوێندرێنەوە)',
                'name_ar'        => 'الحروف التي تكتب ولا تنطق',
                'slug'           => 'silent-letters',
                'tajweed_rule_category_id' => $cat('special-rules'),
                'color_code'     => '#B0BEC5',
                'description_en' => 'Letters that are written in the Quranic text but dropped and not pronounced during recitation (e.g., extra Alifs, Hamzat Wasl in the middle of speech).',
                'description_ku' => 'ئەو پیتانەی لە نووسینی قورئانیدا هەن بەڵام لە کاتی خوێندنەوەدا دەپەڕێندرێن و دەنگیان نییە.',
                'description_ar' => 'الحروف المثبتة رسماً في المصحف ولكنها تسقط في اللفظ والوصل.',
                'example_text'   => 'قَالُوا',
                'priority'       => 101,
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