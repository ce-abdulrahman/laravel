<?php

namespace App\Http\Controllers;

use App\Models\Qiraat;
use Illuminate\Http\Request;

class QiraatController extends Controller
{
    /**
     * Display a listing of the qiraats.
     */
    public function index(Request $request)
    {
        $query = Qiraat::withCount('texts');

        // فلتەر بەپێی ڕیوایەت
        if ($request->filled('riwayah')) {
            $query->where('riwayah', $request->riwayah);
        }

        // گەڕان بەپێی ناو
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // فلتەر بەپێی دۆخ
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $qiraats = $query->orderBy('name')
            ->paginate($request->per_page ?? 12)
            ->withQueryString();

        $riwayahs = Qiraat::distinct()->whereNotNull('riwayah')->pluck('riwayah');

        $stats = [
            'total_qiraats' => Qiraat::count(),
            'active_qiraats' => Qiraat::where('is_active', true)->count(),
            'total_texts' => \App\Models\QiraatText::count(),
        ];

        return view('qiraats.index', compact('qiraats', 'riwayahs', 'stats'));
    }

    /**
     * Show the form for creating a new qiraat.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $riwayahs = $this->getRiwayahs();

        return view('qiraats.create', compact('riwayahs'));
    }

    /**
     * Store a newly created qiraat in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:qiraats,name',
            'riwayah' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $qiraat = Qiraat::create($validated);

        return redirect()
            ->route('qiraats.show', $qiraat)
            ->with('success', __('qiraats.messages.created'));
    }

    /**
     * Display the specified qiraat.
     */
    public function show(Qiraat $qiraat)
    {
        $qiraat->load(['texts' => function ($query) {
            $query->with(['ayah.surah'])->orderBy('ayah_id');
        }]);

        $stats = [
            'total_texts' => $qiraat->texts->count(),
            'surahs_covered' => $qiraat->texts->pluck('ayah.surah_id')->unique()->count(),
        ];

        return view('qiraats.show', compact('qiraat', 'stats'));
    }

    /**
     * Show the form for editing the specified qiraat.
     */
    public function edit(Qiraat $qiraat)
    {
        $this->authorizeAdmin();

        $riwayahs = $this->getRiwayahs();

        return view('qiraats.edit', compact('qiraat', 'riwayahs'));
    }

    /**
     * Update the specified qiraat in storage.
     */
    public function update(Request $request, Qiraat $qiraat)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:qiraats,name,' . $qiraat->id,
            'riwayah' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $qiraat->update($validated);

        return redirect()
            ->route('qiraats.show', $qiraat)
            ->with('success', __('qiraats.messages.updated'));
    }

    /**
     * Remove the specified qiraat from storage.
     */
    public function destroy(Qiraat $qiraat)
    {
        $this->authorizeAdmin();

        // پشکنینی ئایا دەقی هەیە
        if ($qiraat->texts()->count() > 0) {
            return back()->with('error', __('qiraats.messages.has_texts'));
        }

        $qiraat->delete();

        return redirect()
            ->route('qiraats.index')
            ->with('success', __('qiraats.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Qiraat $qiraat)
    {
        $this->authorizeAdmin();

        $qiraat->update(['is_active' => !$qiraat->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $qiraat->is_active,
            'message' => $qiraat->is_active 
                ? __('qiraats.messages.activated') 
                : __('qiraats.messages.deactivated'),
        ]);
    }

    /**
     * Get riwayah list.
     */
    private function getRiwayahs(): array
    {
        return [
            'hafs' => 'حفص عن عاصم',
            'shubah' => 'شعبة عن عاصم',
            'warsh' => 'ورش عن نافع',
            'qaloon' => 'قالون عن نافع',
            'al_doori_abi_amr' => 'الدوري عن أبي عمرو',
            'al_sousi' => 'السوسي عن أبي عمرو',
            'hisham' => 'هشام عن ابن عامر',
            'ibn_zakwan' => 'ابن ذكوان عن ابن عامر',
            'al_bazzi' => 'البزي عن ابن كثير',
            'qunbul' => 'قنبل عن ابن كثير',
            'khalaf' => 'خلف عن حمزة',
            'khallad' => 'خلاد عن حمزة',
            'al_doori_al_kisai' => 'الدوري عن الكسائي',
            'abu_al_harith' => 'أبو الحارث عن الكسائي',
            'ruways' => 'رويس عن يعقوب',
            'rawh' => 'روح عن يعقوب',
            'ishaq' => 'إسحاق عن خلف',
            'idris' => 'إدريس عن خلف',
        ];
    }

    /**
     * Authorize admin access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }
    }
}