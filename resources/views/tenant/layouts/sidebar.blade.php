{{-- ========================= --}}
{{-- Mobile Top Bar + Drawer --}}
{{-- ========================= --}}

{{-- Top bar للموبايل --}}
<div class="md:hidden fixed top-0 inset-x-0 z-40 bg-blue-800 text-white flex items-center justify-between px-4 py-3">
    <div>
        <h1 class="text-lg font-bold">Smart Kodes</h1>
        <p class="text-xs text-blue-200">
            {{ session('tenant_context.current_tenant') ? session('tenant_context.current_tenant')->name : 'Tenant Portal' }}
        </p>
    </div>

    <button id="mobileSidebarToggle"
            type="button"
            class="inline-flex items-center justify-center rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
        <span class="sr-only">Open sidebar</span>
        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>

{{-- Overlay للموبايل --}}
<div id="mobileSidebarOverlay"
     class="fixed inset-0 z-30 bg-black/40 hidden md:hidden"></div>

{{-- Drawer للموبايل --}}
<nav id="mobileSidebar"
     class="fixed inset-y-0 left-0 z-40 w-64 bg-blue-800 text-white transform -translate-x-full transition-transform duration-200 ease-in-out md:hidden flex flex-col">

    <div class="p-4 flex items-start justify-between">
        <div>
            <h1 class="text-xl font-bold">Smart Kodes</h1>
            <p class="text-sm text-blue-200">
                {{ session('tenant_context.current_tenant') ? session('tenant_context.current_tenant')->name : 'Tenant Portal' }}
            </p>
            <p class="text-xs text-blue-300 mt-1">
                {{ Auth::user()->name }}
            </p>
        </div>

        <button id="mobileSidebarClose"
                type="button"
                class="ml-2 text-blue-100 hover:text-white focus:outline-none">
            <span class="sr-only">Close sidebar</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <ul class="mt-4 space-y-1 px-0 flex-1 overflow-y-auto">
        <li>
            <a href="{{ route('tenant.dashboard') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.projects.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.projects.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-project-diagram mr-2"></i>Projects
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.forms.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.forms.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-file-alt mr-2"></i>Forms
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.work-orders.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.work-orders.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-clipboard-list mr-2"></i>Workforce
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.records.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.records.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-database mr-2"></i>Records
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.files.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.files.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-file-upload mr-2"></i>Files
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.users.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.users.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-users mr-2"></i>Users
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.reports.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.reports.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-chart-bar mr-2"></i>Reports
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.billing.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.billing.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-credit-card mr-2"></i>Billing
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.notifications.index') }}"
               class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.notifications.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-bell mr-2"></i>Notifications
            </a>
        </li>
    </ul>

    <div class="px-4 pb-4">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="block w-full text-left px-4 py-2 hover:bg-blue-700 rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>

{{-- ========================= --}}
{{-- Desktop Sidebar (زي القديم) --}}
{{-- ========================= --}}

<nav class="hidden md:flex md:fixed md:top-0 md:left-0 md:h-full md:w-64 md:bg-blue-800 md:text-white md:flex-col md:overflow-y-auto z-40">

    <div class="p-4">
        <h1 class="text-xl font-bold">Smart Kodes</h1>
        <p class="text-sm text-blue-200">
            {{ session('tenant_context.current_tenant') ? session('tenant_context.current_tenant')->name : 'Tenant Portal' }}
        </p>
        <p class="text-xs text-blue-300 mt-1">{{ Auth::user()->name }}</p>
    </div>

    <ul class="mt-4 space-y-1 flex-1">
        <li>
            <a href="{{ route('tenant.dashboard') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.projects.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.projects.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-project-diagram mr-2"></i>Projects
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.forms.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.forms.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-file-alt mr-2"></i>Forms
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.work-orders.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.work-orders.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-clipboard-list mr-2"></i>Workforce
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.records.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.records.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-database mr-2"></i>Records
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.files.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.files.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-file-upload mr-2"></i>Files
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.users.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.users.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-users mr-2"></i>Users
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.reports.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.reports.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-chart-bar mr-2"></i>Reports
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.billing.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.billing.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-credit-card mr-2"></i>Billing
            </a>
        </li>

        <li>
            <a href="{{ route('tenant.notifications.index') }}" class="block px-4 py-2 hover:bg-blue-700 {{ request()->routeIs('tenant.notifications.*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-bell mr-2"></i>Notifications
            </a>
        </li>
    </ul>

    <div class="p-4 border-t border-blue-700">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-blue-700 rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar      = document.getElementById('mobileSidebar');
        const sidebarToggle = document.getElementById('mobileSidebarToggle');
        const sidebarClose  = document.getElementById('mobileSidebarClose');
        const sidebarOverlay = document.getElementById('mobileSidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
    });
</script>
@endpush
