<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WidgetTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'en' => [
                'prayer_widget.title' => 'Prayer Times',
                'prayer_widget.next_prayer' => 'Next Prayer',
                'prayer_widget.remaining_time' => 'Remaining Time',
                'prayer_widget.current_city' => 'Current City',
                'prayer_widget.open_qibla' => 'Qibla Finder',
                'prayer_widget.open_settings' => 'Settings',
                'prayer_widget.refresh' => 'Refresh',
                'prayer_widget.settings.title' => 'Prayer Widget Settings',
                'prayer_widget.settings.enabled' => 'Enable Widget',
                'prayer_widget.settings.visibility' => 'Widget Visibility',
                'prayer_widget.settings.visibility.always' => 'Always Visible',
                'prayer_widget.settings.visibility.auth' => 'Only Authenticated Users',
                'prayer_widget.settings.refresh_interval' => 'Refresh Interval (seconds)',
                'prayer_widget.settings.default_city' => 'Default Fallback City',
                'prayer_widget.settings.display_order' => 'Dashboard Display Order',
                'prayer_widget.settings.hijri_source' => 'Hijri Calendar Source',
                'prayer_widget.settings.save' => 'Save Settings',
                'hijri.month.1' => 'Muharram',
                'hijri.month.2' => 'Safar',
                'hijri.month.3' => 'Rabi\' al-Awwal',
                'hijri.month.4' => 'Rabi\' al-Thani',
                'hijri.month.5' => 'Jumada al-Awwal',
                'hijri.month.6' => 'Jumada al-Thani',
                'hijri.month.7' => 'Rajab',
                'hijri.month.8' => 'Sha\'ban',
                'hijri.month.9' => 'Ramadan',
                'hijri.month.10' => 'Shawwal',
                'hijri.month.11' => 'Dhu al-Qidah',
                'hijri.month.12' => 'Dhu al-Hijjah',
            ],
            'ku' => [
                'prayer_widget.title' => 'کاتی نوێژەکان',
                'prayer_widget.next_prayer' => 'نوێژی داهاتوو',
                'prayer_widget.remaining_time' => 'کاتی ماوە',
                'prayer_widget.current_city' => 'شاری ئێستا',
                'prayer_widget.open_qibla' => 'قیبلەنما',
                'prayer_widget.open_settings' => 'ڕێکخستنەکان',
                'prayer_widget.refresh' => 'نوێکردنەوە',
                'prayer_widget.settings.title' => 'ڕێکخستنی وێجێتی نوێژ',
                'prayer_widget.settings.enabled' => 'چالاککردنی وێجێت',
                'prayer_widget.settings.visibility' => 'بینراویی وێجێت',
                'prayer_widget.settings.visibility.always' => 'هەمیشە دیار بێت',
                'prayer_widget.settings.visibility.auth' => 'تەنها دوای چوونەژوورەوە',
                'prayer_widget.settings.refresh_interval' => 'ماوەی نوێکردنەوە (چرکە)',
                'prayer_widget.settings.default_city' => 'شاری بنەڕەتیی',
                'prayer_widget.settings.display_order' => 'ڕیزبەندی نیشاندان لە داشبۆرد',
                'prayer_widget.settings.hijri_source' => 'سەرچاوەی مێژووی کۆچی',
                'prayer_widget.settings.save' => 'پاشەکەوتکردنی ڕێکخستنەکان',
                'hijri.month.1' => 'موحەڕەم',
                'hijri.month.2' => 'سەفەر',
                'hijri.month.3' => 'ڕەبیعولئەوەڵ',
                'hijri.month.4' => 'ڕەبیعولسانی',
                'hijri.month.5' => 'جومادەلئەوەڵ',
                'hijri.month.6' => 'جومادەسانی',
                'hijri.month.7' => 'ڕەجەب',
                'hijri.month.8' => 'شەعبان',
                'hijri.month.9' => 'ڕەمەزان',
                'hijri.month.10' => 'شەووال',
                'hijri.month.11' => 'زیلقەعدە',
                'hijri.month.12' => 'زیلحەججە',
            ],
            'ar' => [
                'prayer_widget.title' => 'مواقيت الصلاة',
                'prayer_widget.next_prayer' => 'الصلاة القادمة',
                'prayer_widget.remaining_time' => 'الوقت المتبقي',
                'prayer_widget.current_city' => 'المدينة الحالية',
                'prayer_widget.open_qibla' => 'اتجاه القبلة',
                'prayer_widget.open_settings' => 'الإعدادات',
                'prayer_widget.refresh' => 'تحديث',
                'prayer_widget.settings.title' => 'إعدادات ويجت الصلاة',
                'prayer_widget.settings.enabled' => 'تمكين الويجت',
                'prayer_widget.settings.visibility' => 'ظهور الويجت',
                'prayer_widget.settings.visibility.always' => 'ظاهر دائماً',
                'prayer_widget.settings.visibility.auth' => 'للمسجلين فقط',
                'prayer_widget.settings.refresh_interval' => 'فترة التحديث (بالثواني)',
                'prayer_widget.settings.default_city' => 'المدينة الافتراضية',
                'prayer_widget.settings.display_order' => 'ترتيب العرض في لوحة التحكم',
                'prayer_widget.settings.hijri_source' => 'مصدر التقويم الهجري',
                'prayer_widget.settings.save' => 'حفظ الإعدادات',
                'hijri.month.1' => 'المحرم',
                'hijri.month.2' => 'صفر',
                'hijri.month.3' => 'ربيع الأول',
                'hijri.month.4' => 'ربيع الثاني',
                'hijri.month.5' => 'جمادى الأولى',
                'hijri.month.6' => 'جمادى الآخرة',
                'hijri.month.7' => 'رجب',
                'hijri.month.8' => 'شعبان',
                'hijri.month.9' => 'رمضان',
                'hijri.month.10' => 'شوال',
                'hijri.month.11' => 'ذو القعدة',
                'hijri.month.12' => 'ذو الحجة',
            ]
        ];

        DB::transaction(function () use ($translations) {
            foreach ($translations as $langCode => $keys) {
                $language = Language::where('code', $langCode)->first();
                if (!$language) {
                    continue;
                }

                foreach ($keys as $key => $value) {
                    $parts = explode('.', $key);
                    $group = count($parts) > 1 ? $parts[0] : 'general';

                    $translationKey = TranslationKey::firstOrCreate(
                        ['key' => $key],
                        [
                            'group' => $group,
                            'description' => 'Discovered widget translation key',
                        ]
                    );

                    UiTranslation::updateOrCreate(
                        [
                            'translation_key_id' => $translationKey->id,
                            'language_id' => $language->id,
                        ],
                        [
                            'value' => $value,
                            'is_auto_generated' => false,
                        ]
                    );
                }
            }
        });
    }
}
