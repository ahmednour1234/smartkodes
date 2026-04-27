<?php

namespace App\Providers;

use App\Models\File;
use App\Models\Form;
use App\Models\Project;
use App\Models\Record;
use App\Models\User;
use App\Models\WorkOrder;
use App\Policies\FilePolicy;
use App\Policies\FormPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RecordPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class   => ProjectPolicy::class,
        Form::class      => FormPolicy::class,
        WorkOrder::class => WorkOrderPolicy::class,
        Record::class    => RecordPolicy::class,
        File::class      => FilePolicy::class,
        User::class      => UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register model policies
        Gate::policies($this->policies);

        // Super admins (no tenant) bypass all gate checks
        Gate::before(function (User $user, string $ability) {
            if ($user->tenant_id === null) {
                return true;
            }
            return null;
        });

        // Named gates for non-model capabilities
        Gate::define('view-reports', fn (User $user) =>
            $user->tenant_id !== null && $user->isManagerOrAbove()
        );

        Gate::define('export-reports', fn (User $user) =>
            $user->tenant_id !== null && $user->isAdmin()
        );

        Gate::define('manage-billing', fn (User $user) =>
            $user->tenant_id !== null && $user->isAdmin()
        );

        Gate::define('configure-notifications', fn (User $user) =>
            $user->tenant_id !== null && $user->isAdmin()
        );

        Gate::define('manage-settings', fn (User $user) =>
            $user->tenant_id !== null && $user->isAdmin()
        );
    }
}
