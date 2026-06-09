<?php

namespace App\Jobs;

use App\Services\AiTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BatchTranslateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $keys;
    public string $locale;

    /**
     * Create a new job instance.
     */
    public function __construct(array $keys, string $locale)
    {
        $this->keys = $keys;
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     */
    public function handle(AiTranslationService $aiService): void
    {
        $aiService->batchTranslate($this->keys, $this->locale);
    }
}
