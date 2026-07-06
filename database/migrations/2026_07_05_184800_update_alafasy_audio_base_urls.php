<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Reciters audio_base_url
        DB::table('reciters')
            ->where('slug', 'mishary-alafasy')
            ->update([
                'audio_base_url' => 'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee/'
            ]);

        // Update AudioFiles file_path for Mishary Alafasy (reciter_id = 1)
        $alafasy = DB::table('reciters')->where('slug', 'mishary-alafasy')->first();
        if ($alafasy) {
            $audioFiles = DB::table('audio_files')
                ->where('reciter_id', $alafasy->id)
                ->get();

            foreach ($audioFiles as $file) {
                $newPath = str_replace(
                    'https://download.quranicaudio.com/quran/mishari_alafasy/',
                    'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee/',
                    $file->file_path
                );
                DB::table('audio_files')
                    ->where('id', $file->id)
                    ->update(['file_path' => $newPath]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert Reciters audio_base_url
        DB::table('reciters')
            ->where('slug', 'mishary-alafasy')
            ->update([
                'audio_base_url' => 'https://download.quranicaudio.com/quran/mishari_alafasy/'
            ]);

        // Revert AudioFiles file_path for Mishary Alafasy
        $alafasy = DB::table('reciters')->where('slug', 'mishary-alafasy')->first();
        if ($alafasy) {
            $audioFiles = DB::table('audio_files')
                ->where('reciter_id', $alafasy->id)
                ->get();

            foreach ($audioFiles as $file) {
                $newPath = str_replace(
                    'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee/',
                    'https://download.quranicaudio.com/quran/mishari_alafasy/',
                    $file->file_path
                );
                DB::table('audio_files')
                    ->where('id', $file->id)
                    ->update(['file_path' => $newPath]);
            }
        }
    }
};
