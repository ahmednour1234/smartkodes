<div id="adminMobileSidebarOverlay" class="fixed inset-0 z-[90] bg-black/40 hidden lg:hidden"></div>

<nav id="adminMobileSidebar" class="fixed top-0 left-0 h-full w-64 bg-[#008ECC] text-white overflow-y-auto z-[95] transform -translate-x-full lg:translate-x-0 lg:top-[132px] lg:h-[calc(100vh-132px)] transition-transform duration-200 ease-in-out">
    <div class="p-4 flex items-start justify-between bg-white border-b border-gray-200 shadow-sm lg:hidden">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Smart Site Admin</h1>
            <p class="text-sm text-gray-600">
                {{ session('tenant_context.current_tenant') ? session('tenant_context.current_tenant')->name : 'Platform Admin' }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->name }}</p>
        </div>
        <button id="adminMobileSidebarClose" type="button" class="lg:hidden ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
            <span class="sr-only">Close sidebar</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <ul class="mt-0 space-y-1">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.dashboard') ? 'bg-[#0079ad]' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.plans.index') }}" class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.plans.index') ? 'bg-[#0079ad]' : '' }}">
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
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.tenants.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-building mr-2"></i>Subscribers
                </a>
            </li>

            {{-- Users & Roles --}}
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.users.*', 'admin.roles.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-users mr-2"></i>Users & Roles
                </a>
            </li>


            {{-- Notifications --}}
            @if (Route::has('admin.notifications.index'))
                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.notifications.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </a>
                </li>
            @endif

            {{-- Settings --}}
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.settings.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            </li>

        {{-- ======================
             TENANT CONTEXT
           ======================= --}}
        @else

            {{-- Projects --}}
            @if (Route::has('admin.projects.index'))
                <li>
                    <a href="{{ route('admin.projects.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.projects.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-project-diagram mr-2"></i>Projects
                    </a>
                </li>
            @endif

            {{-- Forms --}}
            @if (Route::has('admin.forms.index'))
                <li>
                    <a href="{{ route('admin.forms.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.forms.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-file-alt mr-2"></i>Forms
                    </a>
                </li>
            @endif

            {{-- Categories (Tenant Forms / Records Categories) --}}
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.categories.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </a>
                </li>

            {{-- Work Orders --}}
            @if (Route::has('admin.work-orders.index'))
                <li>
                    <a href="{{ route('admin.work-orders.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.work-orders.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-clipboard-list mr-2"></i>Work Orders
                    </a>
                </li>
            @endif

            {{-- Records --}}
            @if (Route::has('admin.records.index'))
                <li>
                    <a href="{{ route('admin.records.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.records.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-database mr-2"></i>Records
                    </a>
                </li>
            @endif

            {{-- Files --}}
            @if (Route::has('admin.files.index'))
                <li>
                    <a href="{{ route('admin.files.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.files.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-file-upload mr-2"></i>Files
                    </a>
                </li>
            @endif

            {{-- Users & Roles (Tenant Users) --}}
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.users.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-users mr-2"></i>Users & Roles
                </a>
            </li>

            {{-- Reports --}}
            <li>
                <a href="{{ route('admin.reports.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.reports.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </li>

            {{-- Billing --}}
            @if (Route::has('admin.billing.index'))
                <li>
                    <a href="{{ route('admin.billing.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.billing.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-credit-card mr-2"></i>Billing
                    </a>
                </li>
            @endif

            {{-- Notifications --}}
            @if (Route::has('admin.notifications.index'))
                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.notifications.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </a>
                </li>
            @endif

            {{-- Webhooks --}}
            @if (Route::has('admin.webhooks.index'))
                <li>
                    <a href="{{ route('admin.webhooks.index') }}"
                       class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.webhooks.*') ? 'bg-[#0079ad]' : '' }}">
                        <i class="fas fa-plug mr-2"></i>Webhooks
                    </a>
                </li>
            @endif

            {{-- Audit Logs --}}
            <li>
                <a href="{{ route('admin.audit.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.audit.*') ? 'bg-[#0079ad]' : '' }}">
                    <i class="fas fa-history mr-2"></i>Audit Logs
                </a>
            </li>

            {{-- Settings --}}
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="admin-nav-link block px-4 py-2 hover:bg-[#0079ad] {{ request()->routeIs('admin.settings.*') ? 'bg-[#0079ad]' : '' }}">
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
                    class="block w-full text-left px-4 py-2 hover:bg-[#0079ad] rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>
<script>
(function() {
    var sidebar = document.getElementById('adminMobileSidebar');
    var sidebarToggle = document.getElementById('adminMobileSidebarToggle');
    var sidebarClose = document.getElementById('adminMobileSidebarClose');
    var sidebarOverlay = document.getElementById('adminMobileSidebarOverlay');
    function openSidebar() {
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
    }
    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.admin-nav-link').forEach(function(link) {
        link.addEventListener('click', closeSidebar);
    });
})();
</script>
