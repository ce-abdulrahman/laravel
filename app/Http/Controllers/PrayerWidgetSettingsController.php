<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrayerWidgetSettingsController extends Controller
{
    public function index()
    {
        $settings = WidgetSetting::firstOrCreate([]);
        $cities = City::all();
        return view('prayer-widget-settings.index', compact('settings', 'cities'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'widget_enabled' => 'nullable|boolean',
            'widget_visibility' => 'required|string|in:always_visible,only_authenticated',
            'widget_refresh_interval' => 'required|integer|min:60|max:86400',
            'widget_default_city_id' => 'nullable|exists:cities,id',
            'widget_display_order' => 'required|integer|min:0',
            'hijri_source' => 'required|string|in:tabular,umm_al_qura,custom',
        ]);

        $settings = WidgetSetting::firstOrCreate([]);
        $settings->update([
            'widget_enabled' => $request->boolean('widget_enabled'),
            'widget_visibility' => $request->input('widget_visibility'),
            'widget_refresh_interval' => (int) $request->input('widget_refresh_interval'),
            'widget_default_city_id' => $request->input('widget_default_city_id'),
            'widget_display_order' => (int) $request->input('widget_display_order'),
            'hijri_source' => $request->input('hijri_source'),
        ]);

        // Touch settings to update timestamp for API Cache invalidation
        $settings->touch();

        // Clear general cache if relevant
        Cache::forget('widget_settings');

        return redirect()->route('admin.prayer-widget-settings.index')
            ->with('success', __('Settings saved successfully.'));
    }
}
