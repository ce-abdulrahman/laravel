<?php

namespace App\Services;

use App\Models\Reciter;
use InvalidArgumentException;

class RecitationUrlService
{
    /**
     * Generate recitation audio URL dynamically for a given reciter, surah and quality.
     *
     * @param Reciter $reciter
     * @param int $surahNumber
     * @param string $quality
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function generateUrl(Reciter $reciter, int $surahNumber, string $quality = 'high'): string
    {
        if ($surahNumber < 1 || $surahNumber > 114) {
            throw new InvalidArgumentException("Invalid surah number: {$surahNumber}. Must be between 1 and 114.");
        }

        $allowedQualities = ['low', 'medium', 'high'];
        if (!in_array($quality, $allowedQualities, true)) {
            throw new InvalidArgumentException("Invalid quality: {$quality}. Allowed: " . implode(', ', $allowedQualities));
        }

        $baseUrl = $reciter->audio_base_url;
        if (empty($baseUrl)) {
            throw new InvalidArgumentException("Reciter {$reciter->name} does not have an audio base URL configured.");
        }

        // Enforce trailing slash on base URL just in case
        $baseUrl = rtrim($baseUrl, '/') . '/';

        // dynamic url structure: {audio_base_url}{quality}/{surah_number}.mp3
        // For public servers (like QuranicAudio or EveryAyah), we skip the quality subfolder.
        $formattedSurah = sprintf('%03d', $surahNumber);
        if (str_contains($baseUrl, 'quranicaudio.com') || str_contains($baseUrl, 'everyayah.com')) {
            $url = $baseUrl . $formattedSurah . '.mp3';
        } else {
            $url = $baseUrl . $quality . '/' . $formattedSurah . '.mp3';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Generated audio URL is invalid: {$url}");
        }

        return $url;
    }
}
