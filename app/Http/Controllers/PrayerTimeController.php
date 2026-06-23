<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PrayerTime;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PrayerTimeController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Display the prayer times management page with filters.
     */
    public function index(Request $request): View
    {
        $cities = City::orderBy('name')->get();
        $years  = $this->availableYears();

        $cityId    = $request->input('city_id');
        $year      = $request->input('year', date('Y'));
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = PrayerTime::with('city')
            ->orderBy('city_id')
            ->orderBy('date');

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        if ($year) {
            $query->forYear((int) $year);
        }

        if ($dateFrom && $dateTo) {
            $query->forDateRange($dateFrom, $dateTo);
        } elseif ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $prayerTimes = $query->paginate(31)->withQueryString();

        $stats = [
            'total'    => PrayerTime::count(),
            'cities'   => PrayerTime::distinct('city_id')->count('city_id'),
            'years'    => $years,
            'imported' => PrayerTime::where('source', 'import')->count(),
            'manual'   => PrayerTime::where('source', 'manual')->count(),
        ];

        return view('prayer-times.index', compact(
            'prayerTimes', 'cities', 'years', 'cityId', 'year', 'dateFrom', 'dateTo', 'stats'
        ));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeAdmin();
        $cities = City::orderBy('name')->get();
        return view('prayer-times.create', compact('cities'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'date'    => 'required|date_format:Y-m-d',
            'fajr'    => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'sunrise' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'dhuhr'   => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'asr'     => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'maghrib' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'isha'    => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
        ]);

        $validated['year']   = (int) date('Y', strtotime($validated['date']));
        $validated['source'] = 'manual';

        PrayerTime::create($validated);

        return redirect()->route('prayer-times.index')
            ->with('success', 'Prayer time entry created successfully.');
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(PrayerTime $prayerTime): View
    {
        $this->authorizeAdmin();
        $cities = City::orderBy('name')->get();
        return view('prayer-times.edit', compact('prayerTime', 'cities'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, PrayerTime $prayerTime): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'date'    => 'required|date_format:Y-m-d',
            'fajr'    => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'sunrise' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'dhuhr'   => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'asr'     => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'maghrib' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
            'isha'    => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
        ]);

        $validated['year']   = (int) date('Y', strtotime($validated['date']));
        $validated['source'] = 'manual';

        $prayerTime->update($validated);

        return redirect()->route('prayer-times.index')
            ->with('success', 'Prayer time entry updated successfully.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(PrayerTime $prayerTime): RedirectResponse
    {
        $this->authorizeAdmin();
        $prayerTime->delete();

        return redirect()->route('prayer-times.index')
            ->with('success', 'Prayer time entry deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function availableYears(): array
    {
        $dbYears = PrayerTime::distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        $currentYear = (int) date('Y');

        // Always include current + next year in the list even if no data yet
        $allYears = array_unique(array_merge($dbYears, [$currentYear, $currentYear + 1]));
        sort($allYears);

        return $allYears;
    }

    private function authorizeAdmin(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Admin access required.');
        }
    }
}
