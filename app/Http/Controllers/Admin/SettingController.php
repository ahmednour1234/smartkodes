<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings.
     */
    public function index()
    {
        $routeName = request()->route()->getName();

        if (str_starts_with($routeName, 'admin.')) {
            $platformSettings = Cache::get('platform_settings', []);
            $settings = [
                'whatsapp_help_url' => $platformSettings['whatsapp_help_url'] ?? config('services.whatsapp.help_url'),
            ];

            return view('admin.settings.index', compact('settings'));
        }

        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        if (str_starts_with($routeName, 'tenant.')) {
            $tenant = $currentTenant;
            $cached = Cache::get('tenant_settings_' . $currentTenant->id, []);
            foreach (['organization_email' => 'email', 'phone' => 'phone', 'website' => 'website', 'address' => 'address'] as $cacheKey => $attr) {
                if (array_key_exists($cacheKey, $cached)) {
                    $tenant->setAttribute($attr, $cached[$cacheKey]);
                }
            }
            $user = Auth::user();
            $prefs = $user->notification_preferences ?? [];
            $settings = [
                'email_notifications' => $prefs['email_notifications'] ?? true,
                'work_order_notifications' => $prefs['work_order_notifications'] ?? true,
                'form_submission_notifications' => $prefs['form_submission_notifications'] ?? true,
                'project_notifications' => $prefs['project_notifications'] ?? true,
                'billing_notifications' => $prefs['billing_notifications'] ?? true,
            ];
            return view('tenant.settings.index', compact('tenant', 'settings', 'user'));
        }

        abort(404);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $routeName = request()->route()->getName();

        if (str_starts_with($routeName, 'admin.')) {
            $request->validate([
                'whatsapp_help_url' => 'nullable|url|max:255',
            ]);

            $existing = Cache::get('platform_settings', []);
            $platformSettings = array_merge($existing, [
                'whatsapp_help_url' => $request->input('whatsapp_help_url') ?: config('services.whatsapp.help_url'),
            ]);

            Cache::put('platform_settings', $platformSettings, now()->addDays(30));

            return redirect()->route('admin.settings.index')
                ->with('success', 'Platform settings updated successfully.');
        }

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

        Auth::user()->update(['notification_preferences' => $settings]);

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
            'company_logo' => 'nullable|image|max:5120',
        ]);

        $logoPath = $currentTenant->logo_path;
        if ($request->hasFile('company_logo')) {
            if ($currentTenant->logo_path) {
                Storage::disk('public')->delete($currentTenant->logo_path);
            }
            $logoPath = $request->file('company_logo')->store('tenants/logos', 'public');
        }

        $currentTenant->update([
            'name' => $request->organization_name,
            'logo_path' => $logoPath,
        ]);
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
        $tenant = session('tenant_context.current_tenant');
        if ($tenant) {
            Notification::create([
                'tenant_id' => $tenant->id,
                'user_id' => Auth::id(),
                'type' => 'system',
                'title' => 'Password updated',
                'message' => 'Your account password was changed successfully.',
                'data' => [],
                'created_by' => Auth::id(),
            ]);
        }
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
