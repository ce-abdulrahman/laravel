<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\ReminderTemplate;
use App\Models\ReminderTemplateTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ReminderTemplateController — Admin CRUD for reminder templates.
 */
class ReminderTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q');
        $type = $request->input('type');

        $templates = ReminderTemplate::withTrashed(false)
            ->when($q, fn($query) => $query->where(function ($q2) use ($q) {
                $q2->where('key', 'like', "%{$q}%")
                   ->orWhere('type', 'like', "%{$q}%");
            }))
            ->when($type, fn($query) => $query->where('type', $type))
            ->with('translations')
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $types = ReminderTemplate::allTypes();

        return view('reminders.index', compact('templates', 'types'));
    }

    public function create(): View
    {
        $languages = Language::active()->ordered()->get();
        $types     = ReminderTemplate::allTypes();

        return view('reminders.create', compact('languages', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'key'        => 'required|string|max:100|unique:reminder_templates,key',
            'type'       => 'required|string|max:50',
            'icon'       => 'nullable|string|max:20',
            'priority'   => 'nullable|integer|min:1|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ]);

        $template = ReminderTemplate::create([
            'key'        => $request->key,
            'type'       => $request->type,
            'icon'       => $request->input('icon', '🔔'),
            'priority'   => $request->input('priority', 5),
            'sort_order' => $request->input('sort_order', 0),
            'version'    => 1,
            'is_active'  => $request->boolean('is_active', true),
            'metadata'   => null,
        ]);

        // Save translations
        $this->saveTranslations($template, $request->input('translations', []));

        return redirect()->route('reminders.index')
            ->with('success', __('reminders.messages.created'));
    }

    public function edit(ReminderTemplate $reminder): View
    {
        $languages = Language::active()->ordered()->get();
        $types     = ReminderTemplate::allTypes();
        $reminder->load('translations');

        return view('reminders.edit', compact('reminder', 'languages', 'types'));
    }

    public function update(Request $request, ReminderTemplate $reminder): RedirectResponse
    {
        $request->validate([
            'type'       => 'required|string|max:50',
            'icon'       => 'nullable|string|max:20',
            'priority'   => 'nullable|integer|min:1|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ]);

        // Bump version if type/priority changed (triggers Flutter resync)
        $bumpVersion = ($reminder->type !== $request->type)
            || ($reminder->priority !== (int) $request->priority);

        $reminder->update([
            'type'       => $request->type,
            'icon'       => $request->input('icon', $reminder->icon),
            'priority'   => $request->input('priority', $reminder->priority),
            'sort_order' => $request->input('sort_order', $reminder->sort_order),
            'is_active'  => $request->boolean('is_active'),
            'version'    => $bumpVersion ? $reminder->version + 1 : $reminder->version,
        ]);

        $this->saveTranslations($reminder, $request->input('translations', []));

        return redirect()->route('reminders.index')
            ->with('success', __('reminders.messages.updated'));
    }

    public function destroy(ReminderTemplate $reminder): RedirectResponse
    {
        $reminder->delete(); // Soft delete

        return redirect()->route('reminders.index')
            ->with('success', __('reminders.messages.deleted'));
    }

    /**
     * POST /reminders/{reminder}/duplicate — Clone a template.
     */
    public function duplicate(ReminderTemplate $reminder): RedirectResponse
    {
        $clone = $reminder->replicate();
        $clone->key        = $reminder->key . '_copy_' . time();
        $clone->is_active  = false;
        $clone->version    = 1;
        $clone->save();

        // Clone translations
        foreach ($reminder->translations as $t) {
            ReminderTemplateTranslation::create([
                'reminder_template_id' => $clone->id,
                'language_id'          => $t->language_id,
                'field'                => $t->field,
                'value'                => $t->value,
            ]);
        }

        return redirect()->route('reminders.edit', $clone)
            ->with('success', __('reminders.messages.duplicated'));
    }

    // ─── Private ─────────────────────────────────────────────────────────────────

    private function saveTranslations(ReminderTemplate $template, array $translations): void
    {
        $langMap = Language::pluck('id', 'code');

        foreach ($translations as $langCode => $fields) {
            $languageId = $langMap[$langCode] ?? null;
            if (!$languageId) {
                continue;
            }
            foreach ($fields as $field => $value) {
                if (!$value) {
                    continue;
                }
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

        // Bump version when translations change
        $template->increment('version');
    }
}
