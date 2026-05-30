<?php

namespace App\Http\Controllers;

use App\Models\Tasbih;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TasbihController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = Tasbih::query()->orderBy('id');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $tasbihs = $query->paginate(15)->withQueryString();

        return view('tasbihs.index', [
            'tasbihs' => $tasbihs,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('tasbihs.create', [
            'tasbih' => new Tasbih([
                'is_active' => true,
                'target' => 33,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTasbih($request);

        Tasbih::create($validated);

        return redirect()
            ->route('tasbihs.index')
            ->with('success', 'تەسریحەکە بە سەرکەوتوویی دروستکرا.');
    }

    public function edit(Tasbih $tasbih): View
    {
        return view('tasbihs.edit', [
            'tasbih' => $tasbih,
        ]);
    }

    public function update(Request $request, Tasbih $tasbih)
    {
        $validated = $this->validateTasbih($request, $tasbih);

        $tasbih->update($validated);

        return redirect()
            ->route('tasbihs.index')
            ->with('success', 'تەسریحەکە بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function destroy(Tasbih $tasbih)
    {
        $tasbih->delete();

        return redirect()
            ->route('tasbihs.index')
            ->with('success', 'تەسریحەکە بە سەرکەوتوویی سڕایەوە.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTasbih(Request $request, ?Tasbih $tasbih = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target' => ['required', 'integer', 'min:1'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
