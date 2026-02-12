<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $routeName = request()->route()->getName();
        if (str_starts_with($routeName, 'tenant.')) {
            $tenant = $currentTenant;
            $cached = Cache::get('tenant_settings_' . $currentTenant->id, []);
            foreach (['organization_email' => 'email', 'phone' => 'phone', 'website' => 'website', 'address' => 'address'] as $cacheKey => $attr) {
                if (array_key_exists($cacheKey, $cached)) {
                    $tenant->setAttribute($attr, $cached[$cacheKey]);
                }
            }
            $user = Auth::user();
            $settings = [
                'email_notifications' => $cached['email_notifications'] ?? true,
                'work_order_notifications' => $cached['work_order_notifications'] ?? true,
                'form_submission_notifications' => $cached['form_submission_notifications'] ?? true,
                'project_notifications' => $cached['project_notifications'] ?? true,
                'billing_notifications' => $cached['billing_notifications'] ?? true,
            ];
            return view('tenant.settings.index', compact('tenant', 'settings', 'user'));
        }

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
     * Update notification preferences (tenant).
     */
    public function updateNotifications(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $settings = [
            'email_notifications' => $request->boolean('email_notifications'),
            'work_order_notifications' => $request->boolean('work_order_notifications'),
            'form_submission_notifications' => $request->boolean('form_submission_notifications'),
            'project_notifications' => $request->boolean('project_notifications'),
            'billing_notifications' => $request->boolean('billing_notifications'),
        ];

        $existing = Cache::get('tenant_settings_' . $currentTenant->id, []);
        Cache::put('tenant_settings_' . $currentTenant->id, array_merge($existing, $settings), now()->addDays(30));

        return redirect()->route('tenant.settings.index')->withFragment('notifications')
            ->with('success', 'Notification preferences updated.');
    }

    public function updateProfile(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        $currentTenant->update(['name' => $request->organization_name]);
        $existing = Cache::get('tenant_settings_' . $currentTenant->id, []);
        $profile = [
            'organization_email' => $request->organization_email,
            'phone' => $request->phone,
            'website' => $request->website,
            'address' => $request->address,
        ];
        Cache::put('tenant_settings_' . $currentTenant->id, array_merge($existing, $profile), now()->addDays(30));
        return redirect()->route('tenant.settings.index')->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->route('tenant.settings.index')->with('error', 'Current password is incorrect.');
        }
        $request->user()->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        return redirect()->route('tenant.settings.index')->with('success', 'Password updated.');
    }

    public function deleteOrganization()
    {
        return redirect()->route('tenant.settings.index')->with('error', 'Please contact support to delete your organization.');
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
