<?php

namespace App\Services;

use App\Models\TranslationKey;
use Illuminate\Support\Facades\DB;

class TranslationGroupingService
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Auto detect group from key namespace prefix.
     */
    public function autoDetectGroup(string $key): string
    {
        $parts = explode('.', $key);
        return count($parts) > 1 ? strtolower($parts[0]) : 'general';
    }

    /**
     * Rebuild and restructure all translation keys' groups dynamically.
     */
    public function groupRebuild(): array
    {
        $allKeys = TranslationKey::all();
        $updated = [];

        DB::transaction(function () use ($allKeys, &$updated) {
            foreach ($allKeys as $k) {
                $expectedGroup = $this->autoDetectGroup($k->key);
                if ($k->group !== $expectedGroup) {
                    $oldGroup = $k->group;
                    $k->update(['group' => $expectedGroup]);
                    $updated[] = [
                        'key' => $k->key,
                        'old_group' => $oldGroup,
                        'new_group' => $expectedGroup
                    ];
                }
            }
        });

        if (!empty($updated)) {
            $this->translationService->clearCache();
        }

        return [
            'success' => true,
            'total_keys_audited' => count($allKeys),
            'total_groups_rebuilt' => count($updated),
            'rebuild_logs' => $updated
        ];
    }
}
