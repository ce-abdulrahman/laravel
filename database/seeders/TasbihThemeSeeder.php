<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeCategory;
use App\Models\ThemeCategoryTranslation;
use App\Models\ThemeTranslation;
use Illuminate\Database\Seeder;

class TasbihThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = Language::all()->keyBy('code');
        $en = $languages->get('en');
        $ku = $languages->get('ku');
        $ar = $languages->get('ar');

        // 1. Seed Categories
        $categoriesData = [
            [
                'key' => 'islamic',
                'icon' => 'bi bi-moon-stars-fill',
                'sort_order' => 1,
                'names' => [
                    'en' => 'Islamic Themes',
                    'ku' => 'ڕووکارە ئیسلامییەکان',
                    'ar' => 'الثيمات الإسلامية',
                ]
            ],
            [
                'key' => 'nature',
                'icon' => 'bi bi-tree-fill',
                'sort_order' => 2,
                'names' => [
                    'en' => 'Nature Themes',
                    'ku' => 'ڕووکارەکانی سروشت',
                    'ar' => 'ثيمات الطبيعة',
                ]
            ],
            [
                'key' => 'minimal',
                'icon' => 'bi bi-circle-half',
                'sort_order' => 3,
                'names' => [
                    'en' => 'Minimal Themes',
                    'ku' => 'ڕووکاری سادە',
                    'ar' => 'الثيمات البسيطة',
                ]
            ],
            [
                'key' => 'special',
                'icon' => 'bi bi-star-fill',
                'sort_order' => 4,
                'names' => [
                    'en' => 'Special Themes',
                    'ku' => 'ڕووکارە تایبەتەکان',
                    'ar' => 'ثيمات خاصة',
                ]
            ],
            [
                'key' => 'seasonal',
                'icon' => 'bi bi-cloud-sun-fill',
                'sort_order' => 5,
                'names' => [
                    'en' => 'Seasonal Themes',
                    'ku' => 'ڕووکارە وەرزییەکان',
                    'ar' => 'الثيمات الموسمية',
                ]
            ]
        ];

        $categories = [];

        foreach ($categoriesData as $catData) {
            $category = ThemeCategory::create([
                'icon' => $catData['icon'],
                'sort_order' => $catData['sort_order'],
                'is_active' => true,
            ]);

            $categories[$catData['key']] = $category;

            // Seed Translations
            foreach ($catData['names'] as $code => $value) {
                $lang = $languages->get($code);
                if ($lang) {
                    ThemeCategoryTranslation::create([
                        'theme_category_id' => $category->id,
                        'language_id' => $lang->id,
                        'field' => 'name',
                        'value' => $value,
                    ]);
                }
            }
        }

        // 2. Seed Default Themes
        $themesData = [
            [
                'theme_key' => 'kaaba_theme',
                'category_key' => 'islamic',
                'preview_image' => 'assets/themes/kaaba/preview.png',
                'thumbnail' => 'assets/themes/kaaba/thumb.png',
                'is_featured' => true,
                'unlock_type' => 'free',
                'unlock_value' => null,
                'version' => 1,
                'sort_order' => 1,
                'names' => [
                    'en' => 'Kaaba Holy Sanctuary',
                    'ku' => 'کەعبەی پیرۆز',
                    'ar' => 'الكعبة المشرفة',
                ],
                'descriptions' => [
                    'en' => 'A sacred theme depicting the Holy Kaaba with gold and black accents.',
                    'ku' => 'ڕووکارێکی ڕازاوە بە کەعبەی پیرۆز لەگەڵ ڕەنگەکانی زێڕی و ڕەش.',
                    'ar' => 'ثيم مهيب يصور الكعبة المشرفة مع لمسات باللونين الذهبي والأسود.',
                ],
                'metadata' => [
                    'schema_version' => 1,
                    'background' => [
                        'type' => 'image',
                        'value' => 'assets/themes/kaaba/bg.jpg',
                        'animation_speed' => 1.0,
                    ],
                    'counter' => [
                        'design' => 'circular',
                        'background_color' => '#1c1c1e',
                        'text_color' => '#ffd700',
                    ],
                    'ring' => [
                        'color' => '#ffd700',
                        'width' => 10.0,
                        'glow' => true,
                        'animation' => 'ripple',
                    ],
                    'typography' => [
                        'font_family' => 'cairo',
                        'arabic_font' => 'amiri',
                    ],
                    'animation' => [
                        'type' => 'floating_particles',
                        'intensity' => 'medium',
                    ],
                    'sound' => [
                        'type' => 'tasbih_bead',
                        'asset_path' => 'sounds/bead.mp3',
                    ],
                    'haptic' => [
                        'profile' => 'medium',
                    ],
                ]
            ],
            [
                'theme_key' => 'madinah_theme',
                'category_key' => 'islamic',
                'preview_image' => 'assets/themes/madinah/preview.png',
                'thumbnail' => 'assets/themes/madinah/thumb.png',
                'is_featured' => false,
                'unlock_type' => 'points',
                'unlock_value' => '500',
                'version' => 1,
                'sort_order' => 2,
                'names' => [
                    'en' => 'Al-Masjid an-Nabawi',
                    'ku' => 'مزگەوتی پێغەمبەر (د.خ)',
                    'ar' => 'المسجد النبوي الشريف',
                ],
                'descriptions' => [
                    'en' => 'Green dome inspired theme reflecting the peace and light of Madinah.',
                    'ku' => 'ڕووکارێکی ئارام بە ئیلهام وەرگرتن لە گومەزی سەوز و ڕووناکی مەدینە.',
                    'ar' => 'ثيم مستوحى من القبة الخضراء يعكس السلام والنور في المدينة المنورة.',
                ],
                'metadata' => [
                    'schema_version' => 1,
                    'background' => [
                        'type' => 'image',
                        'value' => 'assets/themes/madinah/bg.jpg',
                        'animation_speed' => 1.0,
                    ],
                    'counter' => [
                        'design' => 'ring',
                        'background_color' => '#0c2310',
                        'text_color' => '#81c784',
                    ],
                    'ring' => [
                        'color' => '#2e7d32',
                        'width' => 9.0,
                        'glow' => true,
                        'animation' => 'pulse',
                    ],
                    'typography' => [
                        'font_family' => 'cairo',
                        'arabic_font' => 'amiri',
                    ],
                    'animation' => [
                        'type' => 'glow',
                        'intensity' => 'medium',
                    ],
                    'sound' => [
                        'type' => 'soft_click',
                        'asset_path' => 'sounds/click.mp3',
                    ],
                    'haptic' => [
                        'profile' => 'soft',
                    ],
                ]
            ],
            [
                'theme_key' => 'dark_minimal',
                'category_key' => 'minimal',
                'preview_image' => 'assets/themes/dark_minimal/preview.png',
                'thumbnail' => 'assets/themes/dark_minimal/thumb.png',
                'is_featured' => true,
                'unlock_type' => 'free',
                'unlock_value' => null,
                'version' => 1,
                'sort_order' => 1,
                'names' => [
                    'en' => 'Carbon Minimal',
                    'ku' => 'تاریکی سادە',
                    'ar' => 'تاريك البسيط',
                ],
                'descriptions' => [
                    'en' => 'A highly distraction-free pure dark theme for night usage.',
                    'ku' => 'ڕووکارێکی ڕەشی سادە بە بێ شڵەژان گونجاو بۆ شەوان.',
                    'ar' => 'ثيم داكن بسيط وخالٍ من المشتتات مناسب للاستخدام الليلي.',
                ],
                'metadata' => [
                    'schema_version' => 1,
                    'background' => [
                        'type' => 'gradient',
                        'value' => 'linear-gradient(180deg, #121212 0%, #000000 100%)',
                        'animation_speed' => 0.0,
                    ],
                    'counter' => [
                        'design' => 'minimal',
                        'background_color' => '#1e1e1e',
                        'text_color' => '#ffffff',
                    ],
                    'ring' => [
                        'color' => '#333333',
                        'width' => 6.0,
                        'glow' => false,
                        'animation' => 'none',
                    ],
                    'typography' => [
                        'font_family' => 'inter',
                        'arabic_font' => 'scheherazade',
                    ],
                    'animation' => [
                        'type' => 'scale',
                        'intensity' => 'low',
                    ],
                    'sound' => [
                        'type' => 'silent',
                        'asset_path' => null,
                    ],
                    'haptic' => [
                        'profile' => 'disabled',
                    ],
                ]
            ],
            [
                'theme_key' => 'forest_nature',
                'category_key' => 'nature',
                'preview_image' => 'assets/themes/forest/preview.png',
                'thumbnail' => 'assets/themes/forest/thumb.png',
                'is_featured' => false,
                'unlock_type' => 'streak',
                'unlock_value' => '7',
                'version' => 1,
                'sort_order' => 1,
                'names' => [
                    'en' => 'Zen Forest',
                    'ku' => 'دارستانی هێمن',
                    'ar' => 'الغابة الهادئة',
                ],
                'descriptions' => [
                    'en' => 'Unlock this theme by reaching a 7-day Tasbih streak. Deep green wood colors.',
                    'ku' => 'ئەم ڕووکارە بکەرەوە بە گەیشتن بە زنجیرەی تەسبیحی ٧ ڕۆژە. دارستانی هێمن.',
                    'ar' => 'افتح هذا الثيم بالوصول إلى سلسلة تسبيح لمدة 7 أيام.',
                ],
                'metadata' => [
                    'schema_version' => 1,
                    'background' => [
                        'type' => 'gradient',
                        'value' => 'linear-gradient(135deg, #1b3d2f 0%, #0c1a14 100%)',
                        'animation_speed' => 1.5,
                    ],
                    'counter' => [
                        'design' => 'glassmorphism',
                        'background_color' => 'rgba(25, 50, 40, 0.4)',
                        'text_color' => '#a5d6a7',
                    ],
                    'ring' => [
                        'color' => '#4caf50',
                        'width' => 8.0,
                        'glow' => true,
                        'animation' => 'ripple',
                    ],
                    'typography' => [
                        'font_family' => 'modern',
                        'arabic_font' => 'scheherazade',
                    ],
                    'animation' => [
                        'type' => 'floating_particles',
                        'intensity' => 'high',
                    ],
                    'sound' => [
                        'type' => 'soft_click',
                        'asset_path' => 'sounds/click.mp3',
                    ],
                    'haptic' => [
                        'profile' => 'strong',
                    ],
                ]
            ],
            [
                'theme_key' => 'ramadan_special',
                'category_key' => 'special',
                'preview_image' => 'assets/themes/ramadan/preview.png',
                'thumbnail' => 'assets/themes/ramadan/thumb.png',
                'is_featured' => true,
                'unlock_type' => 'event',
                'unlock_value' => 'ramadan',
                'version' => 1,
                'sort_order' => 1,
                'names' => [
                    'en' => 'Ramadan Crescent',
                    'ku' => 'مانگی ڕەمەزان',
                    'ar' => 'هلال رمضان',
                ],
                'descriptions' => [
                    'en' => 'A spiritual Ramadan special theme with crescent moon and gold lanterns.',
                    'ku' => 'ڕووکارێکی تایبەت بە مانگی پیرۆزی ڕەمەزان، نیشاندەری هیلال و فانۆسی زێڕین.',
                    'ar' => 'ثيم روحاني خاص بشهر رمضان المبارك مع هلال وفوانيس ذهبية.',
                ],
                'metadata' => [
                    'schema_version' => 1,
                    'background' => [
                        'type' => 'image',
                        'value' => 'assets/themes/ramadan/bg.jpg',
                        'animation_speed' => 1.0,
                    ],
                    'counter' => [
                        'design' => 'card',
                        'background_color' => '#140c26',
                        'text_color' => '#ffb300',
                    ],
                    'ring' => [
                        'color' => '#ffb300',
                        'width' => 10.0,
                        'glow' => true,
                        'animation' => 'pulse',
                    ],
                    'typography' => [
                        'font_family' => 'cairo',
                        'arabic_font' => 'amiri',
                    ],
                    'animation' => [
                        'type' => 'glow',
                        'intensity' => 'high',
                    ],
                    'sound' => [
                        'type' => 'tasbih_bead',
                        'asset_path' => 'sounds/bead.mp3',
                    ],
                    'haptic' => [
                        'profile' => 'medium',
                    ],
                ]
            ]
        ];

        foreach ($themesData as $themeData) {
            $cat = $categories[$themeData['category_key']];
            
            $theme = Theme::create([
                'theme_key' => $themeData['theme_key'],
                'category_id' => $cat->id,
                'preview_image' => $themeData['preview_image'],
                'thumbnail' => $themeData['thumbnail'],
                'is_active' => true,
                'is_featured' => $themeData['is_featured'],
                'unlock_type' => $themeData['unlock_type'],
                'unlock_value' => $themeData['unlock_value'],
                'version' => $themeData['version'],
                'theme_metadata' => $themeData['metadata'],
                'sort_order' => $themeData['sort_order'],
            ]);

            // Seed Translations (Name & Description)
            foreach ($themeData['names'] as $code => $value) {
                $lang = $languages->get($code);
                if ($lang) {
                    ThemeTranslation::create([
                        'theme_id' => $theme->id,
                        'language_id' => $lang->id,
                        'field' => 'name',
                        'value' => $value,
                    ]);
                }
            }

            foreach ($themeData['descriptions'] as $code => $value) {
                $lang = $languages->get($code);
                if ($lang) {
                    ThemeTranslation::create([
                        'theme_id' => $theme->id,
                        'language_id' => $lang->id,
                        'field' => 'description',
                        'value' => $value,
                    ]);
                }
            }
        }
    }
}
