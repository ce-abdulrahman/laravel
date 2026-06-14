<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\ReminderTemplate;
use App\Models\ReminderTemplateTranslation;
use Illuminate\Database\Seeder;

/**
 * ReminderTemplateSeeder — Reads templates from config/reminders.php
 *
 * Running this seeder again is safe (uses updateOrCreate).
 * Adding new templates: just add to config/reminders.php, re-run seeder.
 */
class ReminderTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates   = config('reminders.templates', []);
        $langMap     = Language::pluck('id', 'code');

        foreach ($templates as $data) {
            $translations = $data['translations'] ?? [];
            unset($data['translations']);

            // Create or update the template (idempotent)
            $template = ReminderTemplate::updateOrCreate(
                ['key' => $data['key']],
                $data
            );

            // Upsert translations
            foreach ($translations as $langCode => $fields) {
                $languageId = $langMap[$langCode] ?? null;
                if (!$languageId) {
                    continue;
                }

                foreach ($fields as $field => $value) {
                    ReminderTemplateTranslation::updateOrCreate(
                        [
                            'reminder_template_id' => $template->id,
                            'language_id'          => $languageId,
                            'field'                => $field,
                        ],
                        ['value' => $value]
                    );
                }
            }
        }

        $this->command->info('✅ ' . count($templates) . ' reminder templates seeded.');
    }
}
