<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SettingController extends Controller
{
    /**
     * Display settings.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        // For now, we'll use a simple settings array
        // In a real application, you'd have a settings table
        $settings = [
            'tenant_name' => $currentTenant->name,
            'tenant_email' => $currentTenant->email ?? '',
            'timezone' => config('app.timezone'),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'default_language' => 'en',
            'email_notifications' => true,
            'auto_assign_work_orders' => false,
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'nullable|email|max:255',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'default_language' => 'required|string',
            'email_notifications' => 'boolean',
            'auto_assign_work_orders' => 'boolean',
        ]);

        // Update tenant information
        $currentTenant->update([
            'name' => $request->tenant_name,
            'email' => $request->tenant_email,
        ]);

        // Store other settings in cache or database
        // For now, we'll just store in session/cache
        $settings = [
            'timezone' => $request->timezone,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
            'default_language' => $request->default_language,
            'email_notifications' => $request->email_notifications,
            'auto_assign_work_orders' => $request->auto_assign_work_orders,
        ];

        Cache::put('tenant_settings_' . $currentTenant->id, $settings, now()->addDays(30));

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear cache.
     */
    public function clearCache()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        Cache::forget('tenant_settings_' . $currentTenant->id);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Cache cleared successfully.');
    }
}
