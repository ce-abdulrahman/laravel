# Qari Onboarding and Asset Layout Convention

This document describes the onboarding instructions and asset layout conventions for adding new reciters (Qaris) and recitation audio/timing files to the Quran system.

---

## 1. Directory & File Naming Conventions

All recitation audio assets must be organized within the storage system (disk or object storage) using a standardized directory hierarchy and filename convention.

### Audio File Layout

* **Base Folder Location / CDN base URL:**
  `{audio_base_url}/{quality}/{surah_number}.mp3`
  * The `audio_base_url` must end with a `/` character (e.g. `https://audio.example.com/alafasy/` or `http://localhost/storage/reciters/alafasy/`).
  * Under this base URL, directories must exist for the different quality levels:
    * `low/`
    * `medium/`
    * `high/`
  * Within each quality folder, files must use **3-digit zero-padded** surah numbering:
    * `001.mp3` (Al-Fatihah)
    * `002.mp3` (Al-Baqarah)
    * ...
    * `114.mp3` (An-Nas)

Example complete URL:
`https://audio.example.com/alafasy/high/018.mp3` (Surah Al-Kahf in High Quality)

---

## 2. Timing Data Layout and Schema

Ayah timings are loaded dynamically from files stored on disk or object storage. This reduces database size and allows fast loading when playback is initiated.

### Timings Storage Path

Timing JSON files should be stored under the storage directory using the following naming structure:
`timings/{reciter_slug}/surah_{surah_number}.json`

This path must be recorded in the `timing_file_path` column of the `ayah_timings` table.

### Timing JSON Schema

The JSON timing file must contain a sequential array of objects, with timing points defined in seconds (floating-point numbers):

```json
[
  {
    "ayah": 1,
    "start": 0.0,
    "end": 4.3
  },
  {
    "ayah": 2,
    "start": 4.3,
    "end": 8.9
  }
]
```

* **ayah**: Integer representing the Ayah number (1-indexed).
* **start**: Float representing the start time of the Ayah recitation in seconds.
* **end**: Float representing the end time of the Ayah recitation in seconds.

---

## 3. Onboarding a New Reciter

Adding a new reciter to the production system is idempotent and offline-first ready:

1. **Step 1: Database Registration**
   Add a record to the `reciters` table. This can be done via the `ReciterSeeder` (recommended to keep seeders production-ready and idempotent) or through the admin interface:
   ```php
   \App\Models\Reciter::updateOrCreate(
       ['slug' => 'new-qari'],
       [
           'name' => 'New Quran Reciter',
           'riwayah' => 'Hafs',
           'country' => 'Saudi Arabia',
           'language' => 'ar',
           'audio_base_url' => 'https://audio.example.com/new-qari/',
           'supports_ayah_audio' => true,
           'is_active' => true,
       ]
   );
   ```

2. **Step 2: Upload Audio Files**
   Upload the audio files containing low, medium, and high subfolders to the location specified by the `audio_base_url` (object storage bucket or local path).

3. **Step 3: Upload Timing Files & Register Timing Paths**
   * Upload the JSON timing files to the timings storage directory.
   * Add records to the `ayah_timings` table linking the reciter to the surah and recording the path to the timings file:
     ```php
     \App\Models\AyahTiming::create([
         'reciter_id' => $reciterId,
         'surah_id' => $surahId,
         'timing_file_path' => 'timings/new-qari/surah_001.json',
         'duration_seconds' => 35, // optional total duration in seconds
     ]);
     ```

---

## 4. Playback and Fallback Mechanics

When a playback request is initiated, the system checks the `ayah_timings` table for a record matching the `reciter_id` and `surah_id`.
* If a valid timing JSON file is found at `timing_file_path`, the timings are loaded and returned.
* If no timing record exists, or if the JSON file is missing/invalid, the system will **automatically estimate timings** by dividing the surah's `duration_seconds` (or a default calculation of `ayah_count * 5` seconds) evenly among all verses. This ensures uninterrupted recitation and dynamic offline download caching.
