<?php
// app/Helpers/TimeHelper.php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Format seconds to readable time.
     */
    public static function formatSeconds($seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' ' . __('common.seconds');
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        
        return sprintf('%d:%02d', $minutes, $secs);
    }

    /**
     * Format seconds to short format.
     */
    public static function formatShort($seconds): string
    {
        if ($seconds < 3600) {
            return floor($seconds / 60) . 'm';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return $hours . 'h ' . $minutes . 'm';
    }
}