<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard Redirect (Legacy - redirects to appropriate panel)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();

    // Redirect based on user type
    if ($user->tenant_id === null) {
        // Super Admin - redirect to admin dashboard
        return redirect()->route('admin.dashboard');
    } else {
        // Tenant User - redirect to tenant dashboard
        return redirect()->route('tenant.dashboard');
    }
})->middleware(['auth:web'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Super Admin Routes (tenant_id = null only)
|--------------------------------------------------------------------------

*/
Route::prefix('admin')->name('admin.')->group(function () {

            Route::resource('/categories', CategoryController::class)->except('show');
});

Route::middleware(['auth:web', 'tenant'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Tenant Management (Super Admin only)
    Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
    Route::post('tenants/{tenant}/impersonate', [\App\Http\Controllers\Admin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
    Route::post('tenants/stop-impersonation', [\App\Http\Controllers\Admin\TenantController::class, 'stopImpersonation'])->name('tenants.stop-impersonation');
    Route::get('tenants-export', [\App\Http\Controllers\Admin\TenantController::class, 'export'])->name('tenants.export');

    // System Users (All users across all tenants)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('users-export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');

    // System Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    // Plans Management
    Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);

    // Subscriptions Management
    Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class);
    Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\Admin\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('subscriptions/{subscription}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // System-wide Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/submissions-by-status', [\App\Http\Controllers\Admin\ReportController::class, 'submissionsByStatus'])->name('submissions-by-status');
        Route::get('/submissions-over-time', [\App\Http\Controllers\Admin\ReportController::class, 'submissionsOverTime'])->name('submissions-over-time');
        Route::get('/form-analytics', [\App\Http\Controllers\Admin\ReportController::class, 'formAnalytics'])->name('form-analytics');
        Route::get('/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('generate');
    });

    // Global Notifications (Super Admin scope)
    Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class)->only(['index','create','store']);

    // System Audit Logs
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

    // System Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Tenant Routes (tenant_id !== null, tenant-specific data only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Tenant\DashboardController::class, 'index'])->name('dashboard');

    // Projects (tenant-scoped)
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::get('projects-export', [\App\Http\Controllers\Admin\ProjectController::class, 'export'])->name('projects.export');

    // Forms (tenant-scoped)
    Route::resource('forms', \App\Http\Controllers\Admin\FormController::class);
    Route::get('forms/{form}/builder', [\App\Http\Controllers\Admin\FormController::class, 'builder'])->name('forms.builder');
    Route::post('forms/{form}/save-builder', [\App\Http\Controllers\Admin\FormController::class, 'saveBuilder'])->name('forms.save-builder');
    Route::post('forms/{form}/publish', [\App\Http\Controllers\Admin\FormController::class, 'publish'])->name('forms.publish');
    Route::get('forms/{form}/clone', [\App\Http\Controllers\Admin\FormController::class, 'clone'])->name('forms.clone');
    Route::get('form-templates', [\App\Http\Controllers\Admin\FormController::class, 'templates'])->name('forms.templates');
    Route::post('form-templates/import', [\App\Http\Controllers\Admin\FormController::class, 'importTemplate'])->name('forms.import-template');
    Route::get('forms/{form}/export', [\App\Http\Controllers\Admin\FormController::class, 'export'])->name('forms.export');
    Route::get('forms-export', [\App\Http\Controllers\Admin\FormController::class, 'exportList'])->name('forms.export-list');

    // Work Orders (tenant-scoped)
    Route::resource('work-orders', \App\Http\Controllers\Admin\WorkOrderController::class);
    Route::get('work-orders-export', [\App\Http\Controllers\Admin\WorkOrderController::class, 'export'])->name('work-orders.export');

    // Records (tenant-scoped)
    Route::resource('records', \App\Http\Controllers\Admin\RecordController::class);
    Route::get('records-export', [\App\Http\Controllers\Admin\RecordController::class, 'export'])->name('records.export');
    Route::post('records/bulk-export', [\App\Http\Controllers\Admin\RecordController::class, 'bulkExport'])->name('records.bulk-export');
    Route::post('records/bulk-update-status', [\App\Http\Controllers\Admin\RecordController::class, 'bulkUpdateStatus'])->name('records.bulk-update-status');
    Route::delete('records/bulk-delete', [\App\Http\Controllers\Admin\RecordController::class, 'bulkDelete'])->name('records.bulk-delete');
    Route::post('records/{record}/comments', [\App\Http\Controllers\Admin\RecordController::class, 'addComment'])->name('records.add-comment');
    Route::delete('records/{record}/comments/{comment}', [\App\Http\Controllers\Admin\RecordController::class, 'deleteComment'])->name('records.delete-comment');
    Route::post('records/{record}/request-approval', [\App\Http\Controllers\Admin\RecordController::class, 'requestApproval'])->name('records.request-approval');
    Route::post('records/{record}/approvals/{approval}/approve', [\App\Http\Controllers\Admin\RecordController::class, 'approve'])->name('records.approve');
    Route::post('records/{record}/approvals/{approval}/reject', [\App\Http\Controllers\Admin\RecordController::class, 'reject'])->name('records.reject');
    Route::post('records/{record}/assign', [\App\Http\Controllers\Admin\RecordController::class, 'assign'])->name('records.assign');

    // Files (tenant-scoped)
    Route::resource('files', \App\Http\Controllers\Admin\FileController::class);

    // Users (tenant users only)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('users-export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');

    // Reports (tenant-scoped)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/submissions-by-status', [\App\Http\Controllers\Admin\ReportController::class, 'submissionsByStatus'])->name('submissions-by-status');
        Route::get('/submissions-over-time', [\App\Http\Controllers\Admin\ReportController::class, 'submissionsOverTime'])->name('submissions-over-time');
        Route::get('/form-analytics', [\App\Http\Controllers\Admin\ReportController::class, 'formAnalytics'])->name('form-analytics');
        Route::match(['get', 'post'], '/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('generate');
    });

    // Billing (tenant subscription & invoices)
    Route::get('/billing', [\App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing.index');

    // Subscription (tenant view their subscription)
    Route::get('/subscription', [\App\Http\Controllers\Admin\TenantSubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/{id}', [\App\Http\Controllers\Admin\TenantSubscriptionController::class, 'show'])->name('subscription.show');

    // Notifications (tenant-scoped)
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/clear-all', [\App\Http\Controllers\Admin\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/{notification}/unread', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class);

    // Audit Logs (tenant-scoped)
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

    // Settings (tenant settings)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::put('/settings/profile', [\App\Http\Controllers\Admin\SettingController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('/settings/notifications', [\App\Http\Controllers\Admin\SettingController::class, 'updateNotifications'])->name('settings.update-notifications');
    Route::put('/settings/password', [\App\Http\Controllers\Admin\SettingController::class, 'changePassword'])->name('settings.change-password');
    Route::delete('/settings/organization', [\App\Http\Controllers\Admin\SettingController::class, 'deleteOrganization'])->name('settings.delete-organization');
    Route::post('/onboarding/complete', function () {
        session(['onboarding_done' => true]);
        return response()->json(['ok' => true]);
    })->name('onboarding.complete');
});

/*
|--------------------------------------------------------------------------
| Shared Routes (Both admin and tenant users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {
    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Captcha Routes (using mews/captcha package)
|--------------------------------------------------------------------------
*/
Route::get('/captcha/{config?}', '\Mews\Captcha\CaptchaController@getCaptcha')->name('captcha');
Route::get('/captcha/api/{config?}', '\Mews\Captcha\CaptchaController@getCaptchaApi')->name('captcha.api');

// Test captcha route
Route::get('/test-captcha', function () {
    return view('test-captcha');
})->name('test.captcha');

Route::post('/test-captcha', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'captcha' => 'required|captcha'
    ]);
    return back()->with('success', 'Captcha is correct! ✓');
})->name('test.captcha.validate');

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/payment/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/payment/process', [\App\Http\Controllers\PaymentController::class, 'process'])->name('payment.process');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
