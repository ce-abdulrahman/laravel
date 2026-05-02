<?php
// app/Helpers/QuranHelper.php

namespace App\Helpers;

class QuranHelper
{
    /**
     * Get the end mark for an ayah based on its number.
     */
    public static function getAyahEndMark($ayahNumber)
    {
        return '۝' . self::toArabicNumbers($ayahNumber);
    }

    /**
     * Convert numbers to Arabic numerals.
     */
    public static function toArabicNumbers($number)
    {
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($englishNumbers, $arabicNumbers, $number);
    }

    /**
     * Get juz name in Arabic.
     */
    public static function getJuzName($juzNumber)
    {
        $juzNames = [
            1 => 'الجزء الأول',
            2 => 'الجزء الثاني',
            3 => 'الجزء الثالث',
            4 => 'الجزء الرابع',
            5 => 'الجزء الخامس',
            6 => 'الجزء السادس',
            7 => 'الجزء السابع',
            8 => 'الجزء الثامن',
            9 => 'الجزء التاسع',
            10 => 'الجزء العاشر',
            11 => 'الجزء الحادي عشر',
            12 => 'الجزء الثاني عشر',
            13 => 'الجزء الثالث عشر',
            14 => 'الجزء الرابع عشر',
            15 => 'الجزء الخامس عشر',
            16 => 'الجزء السادس عشر',
            17 => 'الجزء السابع عشر',
            18 => 'الجزء الثامن عشر',
            19 => 'الجزء التاسع عشر',
            20 => 'الجزء العشرون',
            21 => 'الجزء الحادي والعشرون',
            22 => 'الجزء الثاني والعشرون',
            23 => 'الجزء الثالث والعشرون',
            24 => 'الجزء الرابع والعشرون',
            25 => 'الجزء الخامس والعشرون',
            26 => 'الجزء السادس والعشرون',
            27 => 'الجزء السابع والعشرون',
            28 => 'الجزء الثامن والعشرون',
            29 => 'الجزء التاسع والعشرون',
            30 => 'الجزء الثلاثون',
        ];

        return $juzNames[$juzNumber] ?? "الجزء {$juzNumber}";
    }

    /**
     * Get hizb name.
     */
    public static function getHizbName($hizbNumber)
    {
        return "الحزب " . self::toArabicNumbers($hizbNumber);
    }
}
