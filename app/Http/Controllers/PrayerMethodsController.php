<?php

namespace App\Http\Controllers;

use App\Models\PrayerMethod;
use App\Models\PrayerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrayerMethodsController extends Controller
{
    public function index()
    {
        $methods = PrayerMethod::orderBy('sort_order')->get();
        $settings = PrayerSetting::firstOrCreate([]);
        return view('prayer-methods.index', compact('methods', 'settings'));
    }

    public function update(Request $request, $id)
    {
        $method = PrayerMethod::findOrFail($id);

        $validated = $request->validate([
            'fajr_angle' => 'nullable|numeric',
            'isha_angle' => 'nullable|numeric',
            'isha_delay_minutes' => 'nullable|integer',
            'isha_delay_ramadan_minutes' => 'nullable|integer',
            'use_diyanet_offsets' => 'nullable|boolean',
            'local_offsets_enabled' => 'nullable|boolean',
            'sort_order' => 'required|integer',
        ]);

        // Build config structure
        $config = [
            'fajr_angle' => isset($validated['fajr_angle']) ? (double) $validated['fajr_angle'] : null,
            'isha_angle' => isset($validated['isha_angle']) ? (double) $validated['isha_angle'] : null,
            'rules' => [],
            'offsets' => new \stdClass(),
        ];

        if (isset($validated['isha_delay_minutes'])) {
            $config['rules']['isha_delay_minutes'] = (int) $validated['isha_delay_minutes'];
        }
        if (isset($validated['isha_delay_ramadan_minutes'])) {
            $config['rules']['isha_delay_ramadan_minutes'] = (int) $validated['isha_delay_ramadan_minutes'];
        }
        if ($request->has('use_diyanet_offsets')) {
            $config['rules']['use_diyanet_offsets'] = (bool) $validated['use_diyanet_offsets'];
        }
        if ($request->has('local_offsets_enabled')) {
            $config['rules']['local_offsets_enabled'] = (bool) $validated['local_offsets_enabled'];
        }

        $method->update([
            'config' => $config,
            'sort_order' => $validated['sort_order'],
        ]);

        // Touch settings for version hashing cache invalidation
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->touch();

        Cache::forget('prayer_methods_list');

        return redirect()->route('admin.prayer-methods.index')->with('success', 'Calculation method updated successfully.');
    }

    public function toggleActive(Request $request, $id)
    {
        $method = PrayerMethod::findOrFail($id);
        
        $settings = PrayerSetting::firstOrCreate([]);
        
        // Prevent disabling the currently active global default method
        if ($method->key === $settings->calculation_method && $method->is_enabled) {
            return redirect()->route('admin.prayer-methods.index')->with('error', 'Cannot disable the currently active default calculation method.');
        }

        $method->update([
            'is_enabled' => !$method->is_enabled
        ]);

        $settings->touch();
        Cache::forget('prayer_methods_list');

        $status = $method->is_enabled ? 'enabled' : 'disabled';
        return redirect()->route('admin.prayer-methods.index')->with('success', "Calculation method {$status} successfully.");
    }

    public function setDefault(Request $request, $id)
    {
        $method = PrayerMethod::findOrFail($id);

        if (!$method->is_enabled) {
            return redirect()->route('admin.prayer-methods.index')->with('error', 'Cannot set a disabled method as the default.');
        }

        $settings = PrayerSetting::firstOrCreate([]);
        $settings->update([
            'calculation_method' => $method->key
        ]);
        $settings->touch();

        Cache::forget('prayer_settings');
        Cache::forget('prayer_methods_list');

        return redirect()->route('admin.prayer-methods.index')->with('success', "Global default calculation method set to {$method->key}.");
    }
}
