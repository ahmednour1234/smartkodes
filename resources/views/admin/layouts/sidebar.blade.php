<nav class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white overflow-y-auto">
    <div class="p-4">
        <h1 class="text-xl font-bold">Smart Kodes Admin</h1>
        <p class="text-sm text-gray-300">
            {{ session('tenant_context.current_tenant') ? session('tenant_context.current_tenant')->name : 'Platform Admin' }}
        </p>
        <p class="text-xs text-gray-400 mt-1">{{ Auth::user()->name }}</p>
    </div>

    <ul class="mt-8 space-y-1">
        {{-- Dashboard --}}
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>
               <li>
            <a href="{{ route('admin.plans.index') }}"
               class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.plans.index') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Plan
            </a>
        </li>

        {{-- ======================
             PLATFORM ADMIN (NO TENANT)
           ======================= --}}
        @if (Auth::user()->tenant_id === null)

            {{-- Subscribers --}}
            <li>
                <a href="{{ route('admin.tenants.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.tenants.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-building mr-2"></i>Subscribers
                </a>
            </li>

            {{-- Users & Roles --}}
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.users.*', 'admin.roles.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i>Users & Roles
                </a>
            </li>

     
            {{-- Notifications --}}
            @if (Route::has('admin.notifications.index'))
                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.notifications.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </a>
                </li>
            @endif

        {{-- ======================
             TENANT CONTEXT
           ======================= --}}
        @else

            {{-- Projects --}}
            @if (Route::has('admin.projects.index'))
                <li>
                    <a href="{{ route('admin.projects.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.projects.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-project-diagram mr-2"></i>Projects
                    </a>
                </li>
            @endif

            {{-- Forms --}}
            @if (Route::has('admin.forms.index'))
                <li>
                    <a href="{{ route('admin.forms.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.forms.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-file-alt mr-2"></i>Forms
                    </a>
                </li>
            @endif

            {{-- Categories (Tenant Forms / Records Categories) --}}
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </a>
                </li>

            {{-- Work Orders --}}
            @if (Route::has('admin.work-orders.index'))
                <li>
                    <a href="{{ route('admin.work-orders.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.work-orders.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-clipboard-list mr-2"></i>Work Orders
                    </a>
                </li>
            @endif

            {{-- Records --}}
            @if (Route::has('admin.records.index'))
                <li>
                    <a href="{{ route('admin.records.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.records.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-database mr-2"></i>Records
                    </a>
                </li>
            @endif

            {{-- Files --}}
            @if (Route::has('admin.files.index'))
                <li>
                    <a href="{{ route('admin.files.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.files.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-file-upload mr-2"></i>Files
                    </a>
                </li>
            @endif

            {{-- Users & Roles (Tenant Users) --}}
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i>Users & Roles
                </a>
            </li>

            {{-- Reports --}}
            <li>
                <a href="{{ route('admin.reports.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </li>

            {{-- Billing --}}
            @if (Route::has('admin.billing.index'))
                <li>
                    <a href="{{ route('admin.billing.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.billing.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-credit-card mr-2"></i>Billing
                    </a>
                </li>
            @endif

            {{-- Notifications --}}
            @if (Route::has('admin.notifications.index'))
                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.notifications.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </a>
                </li>
            @endif

            {{-- Webhooks --}}
            @if (Route::has('admin.webhooks.index'))
                <li>
                    <a href="{{ route('admin.webhooks.index') }}"
                       class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.webhooks.*') ? 'bg-gray-700' : '' }}">
                        <i class="fas fa-plug mr-2"></i>Webhooks
                    </a>
                </li>
            @endif

            {{-- Audit Logs --}}
            <li>
                <a href="{{ route('admin.audit.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.audit.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-history mr-2"></i>Audit Logs
                </a>
            </li>

            {{-- Settings --}}
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            </li>

        @endif
    </ul>

    {{-- Logout --}}
    <div class="absolute bottom-0 w-full p-4">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="block w-full text-left px-4 py-2 hover:bg-gray-700 rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>
