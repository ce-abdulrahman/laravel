<?php

namespace App\Providers;

use App\Services\TranslationService;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

/**
 * LocalizationServiceProvider
 *
 * Overrides the Laravel FileLoader so that __(), trans(), and @lang() resolve
 * from the database (via TranslationService) before falling back to the filesystem.
 *
 * Strategy: Replace the 'translation.loader' binding with our DatabaseLoader,
 * which is called by the Translator when it needs to load a group of translations.
 * This avoids any constructor-timing issues with the Translator itself.
 */
class LocalizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rebind the translation loader used internally by the Translator.
        $this->app->extend('translation.loader', function (FileLoader $original, $app) {
            return new DatabaseLoader($original, $app);
        });
    }

    public function boot(): void
    {
        //
    }
}

/**
 * DatabaseLoader wraps Laravel's FileLoader.
 *
 * When the Translator calls load($locale, $group, $namespace), we intercept
 * and return DB-sourced translations merged with the filesystem fallback.
 *
 * This is safe because the loader is only called after the Translator is
 * fully constructed and app() is available.
 */
class DatabaseLoader implements Loader
{
    protected FileLoader $filesystem;
    protected $app;

    public function __construct(FileLoader $filesystem, $app)
    {
        $this->filesystem = $filesystem;
        $this->app        = $app;
    }

    /**
     * Load the messages for the given locale, group, and namespace.
     *
     * Called by Translator when __('group.key') is first called for a locale.
     * We load from DB first, merge with filesystem, and return.
     */
    public function load($locale, $group, $namespace = null): array
    {
        // Step 1: Load filesystem translations (the fallback base)
        $fsTranslations = $this->filesystem->load($locale, $group, $namespace);

        // Step 2: If namespaced (vendor packages), delegate entirely to filesystem
        if ($namespace && $namespace !== '*') {
            return $fsTranslations;
        }

        // Step 3: Load DB translations for this locale and group
        try {
            /** @var TranslationService $service */
            $service = app(TranslationService::class);
            $dbTranslations = $service->getTranslationsForLocale($locale);

            if (empty($dbTranslations)) {
                return $fsTranslations;
            }

            // Filter to only this group and strip the group prefix
            $prefix    = $group . '.';
            $prefixLen = strlen($prefix);
            $grouped   = [];

            foreach ($dbTranslations as $key => $value) {
                if (str_starts_with($key, $prefix) && $value !== '' && $value !== null) {
                    // 'dashboard.title' → 'title'
                    $subKey          = substr($key, $prefixLen);
                    $grouped[$subKey] = $value;
                }
            }

            // DB values take precedence over filesystem (for keys that exist in DB)
            return array_merge($fsTranslations, $grouped);
        } catch (\Throwable $e) {
            // DB unavailable (e.g. first migration run) — fall back to filesystem
            return $fsTranslations;
        }
    }

    /**
     * Add a new namespace path to the loader.
     */
    public function addNamespace($namespace, $hint): void
    {
        $this->filesystem->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     */
    public function addJsonPath($path): void
    {
        $this->filesystem->addJsonPath($path);
    }

    /**
     * Get an array of all the registered namespaces.
     */
    public function namespaces(): array
    {
        return $this->filesystem->namespaces();
    }

    /**
     * Add a new path to the loader (required by the Loader interface via FileLoader).
     */
    public function addPath($path): void
    {
        if (method_exists($this->filesystem, 'addPath')) {
            $this->filesystem->addPath($path);
        }
    }
}
