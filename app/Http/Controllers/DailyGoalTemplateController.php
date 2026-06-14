<?php

namespace App\Http\Controllers;

use App\Models\DailyGoalTemplate;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyGoalTemplateController extends Controller
{
    /**
     * Display a listing of the daily goal templates.
     */
    public function index(): View
    {
        $templates = DailyGoalTemplate::with('translations')->orderBy('value')->paginate(10);
        $activeLanguages = Language::activeList();

        return view('daily-goal-templates.index', compact('templates', 'activeLanguages'));
    }

    /**
     * Show the form for creating a new daily goal template.
     */
    public function create(): View
    {
        $template = new DailyGoalTemplate(['value' => 100, 'is_active' => true]);
        $activeLanguages = Language::activeList();

        return view('daily-goal-templates.create', compact('template', 'activeLanguages'));
    }

    /**
     * Store a newly created daily goal template in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);
        $validated['is_active'] = $request->boolean('is_active');

        $template = DailyGoalTemplate::create($validated);

        if (isset($validated['translations'])) {
            $template->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()->route('daily-goal-templates.index')
            ->with('success', 'Goal template created successfully.');
    }

    /**
     * Show the form for editing the specified daily goal template.
     */
    public function edit(DailyGoalTemplate $dailyGoalTemplate): View
    {
        $activeLanguages = Language::activeList();

        return view('daily-goal-templates.edit', [
            'template' => $dailyGoalTemplate,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    /**
     * Update the specified daily goal template in storage.
     */
    public function update(Request $request, DailyGoalTemplate $dailyGoalTemplate)
    {
        $validated = $this->validateTemplate($request);
        $validated['is_active'] = $request->boolean('is_active');

        $dailyGoalTemplate->update($validated);

        if (isset($validated['translations'])) {
            $dailyGoalTemplate->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()->route('daily-goal-templates.index')
            ->with('success', 'Goal template updated successfully.');
    }

    /**
     * Remove the specified daily goal template from storage.
     */
    public function destroy(DailyGoalTemplate $dailyGoalTemplate)
    {
        $dailyGoalTemplate->delete();

        return redirect()->route('daily-goal-templates.index')
            ->with('success', 'Goal template deleted successfully.');
    }

    /**
     * Validate the template fields.
     */
    private function validateTemplate(Request $request): array
    {
        $rules = [
            'value'        => ['required', 'integer', 'min:1'],
            'is_active'    => ['nullable'],
            'translations' => ['required', 'array'],
        ];

        foreach (Language::activeList() as $lang) {
            $rules["translations.{$lang->code}.title"] = ['required', 'string', 'max:255'];
            $rules["translations.{$lang->code}.description"] = ['nullable', 'string'];
        }

        return $request->validate($rules);
    }
}
