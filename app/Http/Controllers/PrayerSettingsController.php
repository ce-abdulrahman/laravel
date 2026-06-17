<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PrayerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrayerSettingsController extends Controller
{
    public function index()
    {
        $settings = PrayerSetting::firstOrCreate([]);
        $cities = City::all();
        return view('prayer-settings.index', compact('settings', 'cities'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'calculation_method' => 'required|string',
            'global_notifications_enabled' => 'nullable|boolean',
            'adhan_settings' => 'nullable|array',
        ]);

        $settings = PrayerSetting::firstOrCreate([]);
        $settings->update([
            'calculation_method' => $request->input('calculation_method'),
            'global_notifications_enabled' => $request->has('global_notifications_enabled'),
            'adhan_settings' => $request->input('adhan_settings', []),
        ]);

        Cache::forget('prayer_settings');

        return redirect()->route('admin.prayer-settings.index')->with('success', 'Prayer settings updated successfully.');
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cities,name',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'timezone' => 'required|string',
        ]);

        City::create($validated);
        
        // Touch prayer settings to update its timestamp for ETag versioning
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->touch();

        Cache::forget('prayer_settings_cities');

        return redirect()->route('admin.prayer-settings.index')->with('success', 'City added successfully.');
    }

    public function updateCity(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cities,name,' . $city->id,
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'timezone' => 'required|string',
        ]);

        $city->update($validated);

        // Touch prayer settings to update timestamp for API ETag/version hash
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->touch();

        Cache::forget('prayer_settings_cities');

        return redirect()->route('admin.prayer-settings.index')->with('success', 'City updated successfully.');
    }

    public function destroyCity(City $city)
    {
        $city->delete();

        // Touch prayer settings to update timestamp for API ETag/version hash
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->touch();

        Cache::forget('prayer_settings_cities');

        return redirect()->route('admin.prayer-settings.index')->with('success', 'City deleted successfully.');
    }

    public function clearCache()
    {
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->update([
            'cached_prayer_data' => null,
        ]);
        $settings->touch();

        Cache::forget('prayer_settings');
        Cache::forget('prayer_settings_cities');

        return redirect()->route('admin.prayer-settings.index')->with('success', 'Prayer cache cleared successfully.');
    }
}
