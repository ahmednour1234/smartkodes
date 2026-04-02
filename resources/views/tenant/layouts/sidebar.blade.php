{{-- ========================= --}}
{{-- Mobile Top Bar + Drawer --}}
{{-- ========================= --}}

@php
    $sessionTenant = session('tenant_context.current_tenant');
    $currentTenant = $sessionTenant ? \App\Models\Tenant::find($sessionTenant->id) ?? $sessionTenant : null;
    $currentUser = Auth::user();
@endphp

{{-- Overlay للموبايل --}}
<div id="mobileSidebarOverlay"
     class="fixed inset-0 z-[90] bg-black/40 hidden md:hidden"></div>

{{-- Drawer للموبايل --}}
<nav id="mobileSidebar"
     class="fixed inset-y-0 left-0 z-[95] w-64 bg-[#008ECC] text-white transform -translate-x-full transition-transform duration-200 ease-in-out md:hidden flex flex-col">

    <div class="p-4 flex items-start justify-between">
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/NewIcon.png') }}" alt="Smart Site" class="h-10 w-10 rounded-full object-cover border border-sky-200/70 bg-white">
                <p class="font-semibold">Smart Site</p>
            </div>

            <div class="flex items-center gap-3">
                @if($currentTenant?->logo_url)
                    <img src="{{ $currentTenant->logo_url }}" alt="{{ $currentTenant->name }} logo" class="h-9 w-9 rounded-full object-cover border border-sky-200/70 bg-white">
                @else
                    <div class="h-9 w-9 rounded-full bg-sky-600 border border-sky-200/70 flex items-center justify-center text-sm font-semibold">
                        {{ strtoupper(substr($currentTenant?->name ?? 'C', 0, 1)) }}
                    </div>
                @endif
                <p class="text-sm text-blue-100 truncate max-w-[150px]">{{ $currentTenant?->name ?? 'Company' }}</p>
            </div>

            <div class="flex items-center gap-3">
                @if($currentUser?->photo_url)
                    <img src="{{ $currentUser->photo_url }}" alt="{{ $currentUser->name }}" class="h-9 w-9 rounded-full object-cover border border-sky-200/70 bg-white">
                @else
                    <div class="h-9 w-9 rounded-full bg-sky-600 border border-sky-200/70 flex items-center justify-center text-sm font-semibold">
                        {{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <p class="text-sm text-blue-100 truncate max-w-[150px]">{{ $currentUser?->name ?? 'User' }}</p>
            </div>
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

    <ul class="mt-4 space-y-1 px-0 flex-1 overflow-y-auto" id="mobileSidebarNav">
        <li>
            <a href="{{ route('tenant.dashboard') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.dashboard') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>

        <li class="pt-2">
            <details class="mobile-menu-section" {{ request()->routeIs('tenant.projects.*','tenant.forms.*','tenant.work-orders.*','tenant.records.*') ? 'open' : '' }}>
                <summary class="px-4 py-2 text-xs font-semibold text-sky-100 uppercase tracking-wider cursor-pointer list-none flex items-center justify-between hover:bg-sky-600 rounded">
                    <span>Operations</span>
                    <svg class="w-4 h-4 transition-transform details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <ul class="pl-4 pb-1">
                    <li><a href="{{ route('tenant.projects.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.projects.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-project-diagram mr-2"></i>Projects</a></li>
                    <li><a href="{{ route('tenant.forms.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.forms.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-file-alt mr-2"></i>Forms</a></li>
                    <li><a href="{{ route('tenant.work-orders.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.work-orders.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-clipboard-list mr-2"></i>Work Orders</a></li>
                    <li><a href="{{ route('tenant.records.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.records.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-database mr-2"></i>Records</a></li>
                </ul>
            </details>
        </li>

        <li class="pt-2">
            <details class="mobile-menu-section" {{ request()->routeIs('tenant.users.*') ? 'open' : '' }}>
                <summary class="px-4 py-2 text-xs font-semibold text-sky-100 uppercase tracking-wider cursor-pointer list-none flex items-center justify-between hover:bg-sky-600 rounded">
                    <span>People</span>
                    <svg class="w-4 h-4 transition-transform details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <ul class="pl-4 pb-1">
                    <li><a href="{{ route('tenant.users.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.users.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-users mr-2"></i>Users</a></li>
                </ul>
            </details>
        </li>

        <li class="pt-2">
            <details class="mobile-menu-section" {{ request()->routeIs('tenant.reports.*') ? 'open' : '' }}>
                <summary class="px-4 py-2 text-xs font-semibold text-sky-100 uppercase tracking-wider cursor-pointer list-none flex items-center justify-between hover:bg-sky-600 rounded">
                    <span>Insights</span>
                    <svg class="w-4 h-4 transition-transform details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <ul class="pl-4 pb-1">
                    <li><a href="{{ route('tenant.reports.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.reports.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-chart-bar mr-2"></i>Reports</a></li>
                </ul>
            </details>
        </li>

        <li class="pt-2">
            <details class="mobile-menu-section" {{ request()->routeIs('tenant.files.*','tenant.billing.*','tenant.notifications.*','tenant.settings.*') ? 'open' : '' }}>
                <summary class="px-4 py-2 text-xs font-semibold text-sky-100 uppercase tracking-wider cursor-pointer list-none flex items-center justify-between hover:bg-sky-600 rounded">
                    <span>System</span>
                    <svg class="w-4 h-4 transition-transform details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <ul class="pl-4 pb-1">
                    <li><a href="{{ route('tenant.files.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.files.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-file-upload mr-2"></i>Files</a></li>
                    <li><a href="{{ route('tenant.billing.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.billing.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-credit-card mr-2"></i>Billing</a></li>
                    <li><a href="{{ route('tenant.notifications.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.notifications.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-bell mr-2"></i>Notifications</a></li>
                    <li><a href="{{ route('tenant.settings.index') }}" class="mobile-nav-link block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.settings.*') ? 'bg-sky-600' : '' }}"><i class="fas fa-cog mr-2"></i>Settings</a></li>
                </ul>
            </details>
        </li>
    </ul>

    <div class="px-4 pb-4">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="block w-full text-left px-4 py-2 hover:bg-sky-600 rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>

{{-- ========================= --}}
{{-- Desktop Sidebar (زي القديم) --}}
{{-- ========================= --}}

<nav class="hidden md:flex md:fixed md:top-[132px] md:left-0 md:h-[calc(100vh-132px)] md:w-64 md:bg-[#008ECC] md:text-white md:flex-col md:overflow-y-auto sidebar-scroll z-40">

    <ul class="mt-0 space-y-1 flex-1">
        <li>
            <a href="{{ route('tenant.dashboard') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.dashboard') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        </li>

        <li class="pt-2">
            <p class="px-4 py-1 text-xs font-semibold text-sky-100 uppercase tracking-wider">Operations</p>
        </li>
        <li>
            <a href="{{ route('tenant.projects.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.projects.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-project-diagram mr-2"></i>Projects
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.forms.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.forms.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-file-alt mr-2"></i>Forms
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.work-orders.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.work-orders.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-clipboard-list mr-2"></i>Work Orders
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.records.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.records.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-database mr-2"></i>Records
            </a>
        </li>

        <li class="pt-2">
            <p class="px-4 py-1 text-xs font-semibold text-sky-100 uppercase tracking-wider">People</p>
        </li>
        <li>
            <a href="{{ route('tenant.users.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.users.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-users mr-2"></i>Users
            </a>
        </li>

        <li class="pt-2">
            <p class="px-4 py-1 text-xs font-semibold text-sky-100 uppercase tracking-wider">Insights</p>
        </li>
        <li>
            <a href="{{ route('tenant.reports.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.reports.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-chart-bar mr-2"></i>Reports
            </a>
        </li>

        <li class="pt-2">
            <p class="px-4 py-1 text-xs font-semibold text-sky-100 uppercase tracking-wider">System</p>
        </li>
        <li>
            <a href="{{ route('tenant.files.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.files.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-file-upload mr-2"></i>Files
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.billing.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.billing.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-credit-card mr-2"></i>Billing
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.notifications.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.notifications.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-bell mr-2"></i>Notifications
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.settings.index') }}" class="block px-4 py-2 hover:bg-sky-600 {{ request()->routeIs('tenant.settings.*') ? 'bg-sky-600' : '' }}">
                <i class="fas fa-cog mr-2"></i>Settings
            </a>
        </li>
    </ul>

    <div class="p-4 border-t border-sky-600">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-sky-600 rounded transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</nav>

@push('styles')
<style>
#mobileSidebar details[open] .details-chevron { transform: rotate(180deg); }
.sidebar-scroll::-webkit-scrollbar { width: 6px; }
.sidebar-scroll::-webkit-scrollbar-track { background: #0079ad; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: #006f9f; border-radius: 3px; }
.sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #005f88; }
.sidebar-scroll { scrollbar-width: thin; scrollbar-color: #006f9f #0079ad; }
</style>
@endpush
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

        document.querySelectorAll('#mobileSidebar .mobile-nav-link').forEach(function (link) {
            link.addEventListener('click', closeSidebar);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
    });
</script>
@endpush
