<?php

namespace App\Services;

class PrayerCalculationService
{
    /**
     * Compute Julian Date for a Gregorian date.
     */
    public function getJulianDate(int $year, int $month, int $day, float $hour = 12.0): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);
        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $B - 1524.5 + ($hour / 24.0);
    }

    /**
     * Compute Solar Declination and Equation of Time (in hours).
     */
    public function getSunDeclinationAndEqT(float $jd): array
    {
        $d = $jd - 2451545.0;
        
        // Solar Mean Anomaly
        $g = 357.529 + 0.98560028 * $d;
        // Solar Mean Longitude
        $q = 280.459 + 0.98564736 * $d;
        
        $gRad = deg2rad($g);
        // Solar Apparent Longitude
        $L = $q + 1.915 * sin($gRad) + 0.020 * sin(2 * $gRad);
        
        // Obliquity of Ecliptic
        $e = 23.439 - 0.00000036 * $d;
        
        $eRad = deg2rad($e);
        $LRad = deg2rad($L);
        
        // Solar Declination
        $dec = rad2deg(asin(sin($eRad) * sin($LRad)));
        
        // Solar Right Ascension
        $ra = rad2deg(atan2(cos($eRad) * sin($LRad), cos($LRad)));
        $ra = $this->normalizeAngle($ra);
        
        // Equation of Time (EqT)
        $diff = $q - $ra;
        while ($diff < -180) {
            $diff += 360;
        }
        while ($diff > 180) {
            $diff -= 360;
        }
        $eqt = $diff / 15.0; // convert to hours
        
        return [$dec, $eqt];
    }

    /**
     * Calculate prayer times for coordinates and custom method parameters.
     * Returns an associative array of prayer times as decimal hours (relative to local time).
     */
    public function calculateTimes(
        float $latitude,
        float $longitude,
        float $utcOffset,
        int $year,
        int $month,
        int $day,
        float $fajrAngle,
        ?float $ishaAngle,
        ?int $ishaDelay = null,
        int $madhab = 1
    ): array {
        // Julian date at local noon
        $jd = $this->getJulianDate($year, $month, $day, 12.0 - $longitude / 15.0);
        [$dec, $eqt] = $this->getSunDeclinationAndEqT($jd);
        
        // Base local transit (midday / Dhuhr base)
        $transitLocal = 12.0 + $utcOffset - $longitude / 15.0 - $eqt;
        
        // 1. Dhuhr (transit + small safety buffer of 1 min)
        $dhuhr = $transitLocal + (1.0 / 60.0);
        
        // 2. Sunrise & Maghrib (Sunset)
        // Sunset angle is -0.8333 degrees (includes refraction and sun semi-diameter)
        $sunriseAngle = -0.8333;
        $cosH_M = (sin(deg2rad($sunriseAngle)) - sin(deg2rad($latitude)) * sin(deg2rad($dec))) 
                  / (cos(deg2rad($latitude)) * cos(deg2rad($dec)));
                  
        if ($cosH_M < -1 || $cosH_M > 1) {
            // Extreme latitudes fallback (polar day/night)
            $H_M = 0;
        } else {
            $H_M = rad2deg(acos($cosH_M));
        }
        
        $sunrise = $transitLocal - ($H_M / 15.0);
        $maghrib = $transitLocal + ($H_M / 15.0);
        
        // 3. Fajr Hour Angle
        $cosH_F = (sin(deg2rad(-$fajrAngle)) - sin(deg2rad($latitude)) * sin(deg2rad($dec))) 
                  / (cos(deg2rad($latitude)) * cos(deg2rad($dec)));
                  
        if ($cosH_F < -1 || $cosH_F > 1) {
            $H_F = 0;
        } else {
            $H_F = rad2deg(acos($cosH_F));
        }
        $fajr = $transitLocal - ($H_F / 15.0);
        
        // 4. Asr Hour Angle (shadow math)
        // Shadow length equation
        $asrAngle = rad2deg(atan(1.0 / ($madhab + tan(deg2rad(abs($latitude - $dec))))));
        $cosH_A = (sin(deg2rad($asrAngle)) - sin(deg2rad($latitude)) * sin(deg2rad($dec))) 
                  / (cos(deg2rad($latitude)) * cos(deg2rad($dec)));
                  
        if ($cosH_A < -1 || $cosH_A > 1) {
            $H_A = 0;
        } else {
            $H_A = rad2deg(acos($cosH_A));
        }
        $asr = $transitLocal + ($H_A / 15.0);
        
        // 5. Isha Hour Angle or Fixed Delay
        if ($ishaAngle !== null) {
            $cosH_I = (sin(deg2rad(-$ishaAngle)) - sin(deg2rad($latitude)) * sin(deg2rad($dec))) 
                      / (cos(deg2rad($latitude)) * cos(deg2rad($dec)));
                      
            if ($cosH_I < -1 || $cosH_I > 1) {
                $H_I = 0;
            } else {
                $H_I = rad2deg(acos($cosH_I));
            }
            $isha = $transitLocal + ($H_I / 15.0);
        } else {
            // Fixed delay from Maghrib (e.g. Umm al-Qura)
            $isha = $maghrib + (($ishaDelay ?? 90) / 60.0);
        }
        
        return [
            'fajr' => $fajr,
            'sunrise' => $sunrise,
            'dhuhr' => $dhuhr,
            'asr' => $asr,
            'maghrib' => $maghrib,
            'isha' => $isha,
        ];
    }

    private function normalizeAngle(float $angle): float
    {
        $angle = fmod($angle, 360.0);
        if ($angle < 0) {
            $angle += 360.0;
        }
        return $angle;
    }
}
