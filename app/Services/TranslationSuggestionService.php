<?php

namespace App\Services;

use App\Models\TranslationKey;

class TranslationSuggestionService
{
    /**
     * Suggest keys based on clean English text.
     */
    public function suggestKey(string $text): array
    {
        $text = trim($text);
        if (empty($text)) {
            return [];
        }

        // 1. Clean and tokenize text
        $clean = preg_replace('/[^a-z0-9\s]/', '', strtolower($text));
        $words = array_values(array_filter(explode(' ', $clean)));

        // Remove stopwords
        $stopwords = ['a', 'an', 'the', 'is', 'are', 'was', 'were', 'for', 'of', 'in', 'on', 'at', 'to', 'from', 'with', 'by', 'this', 'that'];
        $words = array_values(array_filter($words, fn($w) => !in_array($w, $stopwords, true)));

        if (empty($words)) {
            return [];
        }

        // 2. Classify module/group based on keywords
        $moduleMap = [
            'auth' => ['login', 'logout', 'signin', 'signup', 'register', 'password', 'email', 'verify', 'reset', 'forgot'],
            'settings' => ['setting', 'settings', 'config', 'theme', 'preference', 'preferences', 'profile'],
            'menu' => ['menu', 'sidebar', 'nav', 'navigation', 'link', 'header', 'footer'],
            'errors' => ['error', 'failed', 'invalid', 'warning', 'danger', 'exception', 'alert'],
            'home' => ['welcome', 'home', 'intro', 'landing', 'title', 'dashboard'],
        ];

        $detectedModule = 'general';
        foreach ($moduleMap as $module => $keywords) {
            foreach ($keywords as $kw) {
                if (in_array($kw, $words, true)) {
                    $detectedModule = $module;
                    break 2;
                }
            }
        }

        // 3. Classify action/element
        $actions = ['save', 'submit', 'update', 'delete', 'create', 'remove', 'add', 'edit', 'cancel', 'close', 'open', 'show', 'hide'];
        $detectedAction = null;
        foreach ($actions as $act) {
            if (in_array($act, $words, true)) {
                $detectedAction = $act;
                break;
            }
        }

        // Filter out action and module keyword from context words if possible
        $contextWords = array_filter($words, function($w) use ($detectedModule, $detectedAction, $moduleMap) {
            if ($w === $detectedAction) return false;
            // Also filter out standard module keywords to avoid duplicates like auth.login.login
            if (isset($moduleMap[$detectedModule]) && in_array($w, $moduleMap[$detectedModule], true)) {
                return false;
            }
            return true;
        });

        if (empty($contextWords)) {
            // Fallback to words themselves if everything was filtered
            $contextWords = $words;
        }

        $contextStr = implode('_', $contextWords);
        
        // 4. Construct suggested dot-notated key templates
        $suggestions = [];

        // Pattern A: module.context.action
        if ($detectedAction) {
            $suggestions[] = "{$detectedModule}.{$contextStr}.{$detectedAction}";
        } else {
            $suggestions[] = "{$detectedModule}.{$contextStr}";
        }

        // Pattern B: module.action_context
        if ($detectedAction) {
            $suggestions[] = "{$detectedModule}.{$detectedAction}_{$contextStr}";
        } else {
            $suggestions[] = "{$detectedModule}.{$contextStr}_label";
        }

        // Pattern C: context.action (simplified)
        if ($detectedAction && $detectedModule !== 'general') {
            $suggestions[] = "{$contextStr}.{$detectedAction}";
        } else {
            $suggestions[] = "common.{$contextStr}";
        }

        // Ensure unique list
        $suggestions = array_unique($suggestions);

        $results = [];
        foreach ($suggestions as $sug) {
            $results[] = [
                'key' => $sug,
                'exists' => TranslationKey::where('key', $sug)->exists(),
            ];
        }

        return $results;
    }
}
