{{-- ========================= --}}
{{-- Mobile Top Bar + Drawer --}}
{{-- ========================= --}}

@php
    $sessionTenant = session('tenant_context.current_tenant');
    $currentTenant = $sessionTenant ? \App\Models\Tenant::find($sessionTenant->id) ?? $sessionTenant : null;
    $currentUser = Auth::user();
@endphp

{{-- Mobile Overlay --}}
<div id="mobileSidebarOverlay"
     class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm hidden md:hidden transition-opacity duration-300"></div>

{{-- Mobile Drawer --}}
<nav id="mobileSidebar"
     class="fixed inset-y-0 left-0 z-[95] w-72 sidebar-bg text-white transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden flex flex-col shadow-2xl">

    {{-- Mobile Header --}}
    <div class="px-5 pt-5 pb-4 border-b border-white/10">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                    <img src="{{ asset('assets/NewIcon.png') }}" alt="Smart Site" class="h-5 w-5 object-contain">
                </div>
                <span class="font-bold text-sm tracking-wide">Smart Site</span>
            </div>
            <button id="mobileSidebarClose" type="button"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors focus:outline-none">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tenant & User chips --}}
        <div class="space-y-2">
            <div class="flex items-center gap-2.5 bg-white/10 rounded-xl px-3 py-2">
                @if($currentTenant?->logo_url)
                    <img src="{{ $currentTenant->logo_url }}" alt="{{ $currentTenant->name }}" class="h-7 w-7 rounded-lg object-cover">
                @else
                    <div class="h-7 w-7 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($currentTenant?->name ?? 'C', 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-[10px] text-white/50 uppercase tracking-wider leading-none mb-0.5">Workspace</p>
                    <p class="text-xs font-medium truncate">{{ $currentTenant?->name ?? 'Company' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5 bg-white/10 rounded-xl px-3 py-2">
                @if($currentUser?->photo_url)
                    <img src="{{ $currentUser->photo_url }}" alt="{{ $currentUser->name }}" class="h-7 w-7 rounded-lg object-cover">
                @else
                    <div class="h-7 w-7 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-[10px] text-white/50 uppercase tracking-wider leading-none mb-0.5">Signed in as</p>
                    <p class="text-xs font-medium truncate">{{ $currentUser?->name ?? 'User' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <ul class="mt-3 space-y-0.5 px-3 flex-1 overflow-y-auto sidebar-scroll" id="mobileSidebarNav">

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('tenant.dashboard') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.dashboard') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-home text-base"></i></span>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Operations --}}
        <li class="pt-3">
            <p class="sidebar-section-label px-3 mb-1">Operations</p>
        </li>
        <li>
            <a href="{{ route('tenant.projects.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.projects.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                <span>Projects</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.forms.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.forms.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-wpforms text-base"></i></span>
                <span>Forms</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.work-orders.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.work-orders.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-tools text-base"></i></span>
                <span>Work Orders</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.records.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.records.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-table text-base"></i></span>
                <span>Records</span>
            </a>
        </li>

        {{-- People --}}
        <li class="pt-3">
            <p class="sidebar-section-label px-3 mb-1">People</p>
        </li>
        <li>
            <a href="{{ route('tenant.users.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.users.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                <span>Users</span>
            </a>
        </li>

        {{-- Insights --}}
        <li class="pt-3">
            <p class="sidebar-section-label px-3 mb-1">Insights</p>
        </li>
        <li>
            <a href="{{ route('tenant.reports.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.reports.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-chart-area text-base"></i></span>
                <span>Reports</span>
            </a>
        </li>

        {{-- System --}}
        <li class="pt-3">
            <p class="sidebar-section-label px-3 mb-1">System</p>
        </li>
        <li>
            <a href="{{ route('tenant.files.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.files.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-folder-open text-base"></i></span>
                <span>Files</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.billing.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.billing.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-credit-card text-base"></i></span>
                <span>Billing</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.notifications.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.notifications.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                <span>Notifications</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.settings.index') }}"
               class="mobile-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.settings.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                <span>Settings</span>
            </a>
        </li>

        <li class="pb-3"></li>
    </ul>

    {{-- Mobile Logout --}}
    <div class="px-3 pb-4 border-t border-white/10 pt-3">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="nav-item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium nav-default hover:bg-red-500/20 hover:text-red-200 transition-all duration-150">
                <span class="nav-icon-wrap"><i class="fas fa-sign-out-alt text-base"></i></span>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</nav>

{{-- ========================= --}}
{{-- Desktop Sidebar           --}}
{{-- ========================= --}}

<nav class="hidden md:flex md:fixed md:top-[132px] md:left-0 md:h-[calc(100vh-132px)] md:w-64 sidebar-bg md:text-white md:flex-col md:overflow-y-auto sidebar-scroll z-40">

    <ul class="mt-2 space-y-0.5 px-3 flex-1">

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('tenant.dashboard') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.dashboard') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-home text-base"></i></span>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Operations --}}
        <li class="pt-4">
            <p class="sidebar-section-label px-3 mb-1">Operations</p>
        </li>
        <li>
            <a href="{{ route('tenant.projects.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.projects.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                <span>Projects</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.forms.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.forms.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-wpforms text-base"></i></span>
                <span>Forms</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.work-orders.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.work-orders.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-tools text-base"></i></span>
                <span>Work Orders</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.records.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.records.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-table text-base"></i></span>
                <span>Records</span>
            </a>
        </li>

        {{-- People --}}
        <li class="pt-4">
            <p class="sidebar-section-label px-3 mb-1">People</p>
        </li>
        <li>
            <a href="{{ route('tenant.users.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.users.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                <span>Users</span>
            </a>
        </li>

        {{-- Insights --}}
        <li class="pt-4">
            <p class="sidebar-section-label px-3 mb-1">Insights</p>
        </li>
        <li>
            <a href="{{ route('tenant.reports.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.reports.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-chart-area text-base"></i></span>
                <span>Reports</span>
            </a>
        </li>

        {{-- System --}}
        <li class="pt-4">
            <p class="sidebar-section-label px-3 mb-1">System</p>
        </li>
        <li>
            <a href="{{ route('tenant.files.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.files.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-folder-open text-base"></i></span>
                <span>Files</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.billing.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.billing.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-credit-card text-base"></i></span>
                <span>Billing</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.notifications.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.notifications.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                <span>Notifications</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tenant.settings.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('tenant.settings.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                <span>Settings</span>
            </a>
        </li>

        <li class="pb-3"></li>
    </ul>

    {{-- Desktop Logout --}}
    <div class="px-3 pb-4 border-t border-white/10 pt-3">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="nav-item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium nav-default hover:bg-red-500/20 hover:text-red-200 transition-all duration-150">
                <span class="nav-icon-wrap"><i class="fas fa-sign-out-alt text-base"></i></span>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</nav>

<style>
/* ── Sidebar background ─────────────────────────────────── */
.sidebar-bg {
    background: linear-gradient(180deg, #005f8e 0%, #004a72 50%, #003a5c 100%);
}

/* ── Section labels ─────────────────────────────────────── */
.sidebar-section-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.40);
}

/* ── Nav item base ──────────────────────────────────────── */
.nav-default {
    color: rgba(255,255,255,0.72);
}
.nav-default:hover {
    background: rgba(255,255,255,0.10);
    color: #ffffff;
}

/* ── Active nav item ────────────────────────────────────── */
.nav-active {
    background: rgba(255,255,255,0.18);
    color: #ffffff;
    box-shadow: inset 3px 0 0 #7dd3fc;
}

/* ── Icon wrapper ───────────────────────────────────────── */
.nav-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.5rem;
    background: rgba(255,255,255,0.08);
    flex-shrink: 0;
    font-size: 0.8rem;
}
.nav-active .nav-icon-wrap {
    background: rgba(125, 211, 252, 0.25);
    color: #7dd3fc;
}
.nav-default:hover .nav-icon-wrap {
    background: rgba(255,255,255,0.15);
}

/* ── Scrollbar ──────────────────────────────────────────── */
.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
.sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar       = document.getElementById('mobileSidebar');
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
        if (sidebarClose)  sidebarClose.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        document.querySelectorAll('#mobileSidebar .mobile-nav-link').forEach(function (link) {
            link.addEventListener('click', closeSidebar);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
    });
</script>
