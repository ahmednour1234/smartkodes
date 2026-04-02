{{-- ========================= --}}
{{-- Mobile Overlay           --}}
{{-- ========================= --}}
<div id="adminMobileSidebarOverlay"
     class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm hidden lg:hidden transition-opacity duration-300"></div>

{{-- ========================= --}}
{{-- Mobile Drawer             --}}
{{-- ========================= --}}
<nav id="adminMobileSidebar"
     class="fixed inset-y-0 left-0 z-[95] w-72 sidebar-bg text-white transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden flex flex-col shadow-2xl">

    {{-- Mobile Header --}}
    <div class="px-5 pt-5 pb-4 border-b border-white/10">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                    <img src="{{ asset('assets/NewIcon.png') }}" alt="Smart Site" class="h-5 w-5 object-contain">
                </div>
                <span class="font-bold text-sm tracking-wide">Smart Site Admin</span>
            </div>
            <button id="adminMobileSidebarClose" type="button"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors focus:outline-none">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="bg-white/10 rounded-xl px-3 py-2">
            <p class="text-[10px] text-white/50 uppercase tracking-wider leading-none mb-0.5">Signed in as</p>
            <p class="text-xs font-medium truncate">{{ Auth::user()->name }}</p>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <ul class="mt-3 space-y-0.5 px-3 flex-1 overflow-y-auto sidebar-scroll">

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-home text-base"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.plans.index') }}"
               class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.plans.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                <span>Plans</span>
            </a>
        </li>

        @if (Auth::user()->tenant_id === null)
            {{-- Platform Admin --}}
            <li class="pt-3"><p class="sidebar-section-label px-3 mb-1">Platform</p></li>
            <li>
                <a href="{{ route('admin.tenants.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.tenants.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-building text-base"></i></span>
                    <span>Subscribers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.users.*','admin.roles.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                    <span>Users &amp; Roles</span>
                </a>
            </li>
            @if (Route::has('admin.notifications.index'))
            <li>
                <a href="{{ route('admin.notifications.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.notifications.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                    <span>Notifications</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                    <span>Settings</span>
                </a>
            </li>

        @else
            {{-- Tenant Context --}}
            <li class="pt-3"><p class="sidebar-section-label px-3 mb-1">Operations</p></li>
            @if (Route::has('admin.projects.index'))
            <li>
                <a href="{{ route('admin.projects.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.projects.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                    <span>Projects</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.forms.index'))
            <li>
                <a href="{{ route('admin.forms.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.forms.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-wpforms text-base"></i></span>
                    <span>Forms</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.categories.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-tags text-base"></i></span>
                    <span>Categories</span>
                </a>
            </li>
            @if (Route::has('admin.work-orders.index'))
            <li>
                <a href="{{ route('admin.work-orders.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.work-orders.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-tools text-base"></i></span>
                    <span>Work Orders</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.records.index'))
            <li>
                <a href="{{ route('admin.records.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.records.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-table text-base"></i></span>
                    <span>Records</span>
                </a>
            </li>
            @endif

            <li class="pt-3"><p class="sidebar-section-label px-3 mb-1">People</p></li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                    <span>Users &amp; Roles</span>
                </a>
            </li>

            <li class="pt-3"><p class="sidebar-section-label px-3 mb-1">Insights</p></li>
            <li>
                <a href="{{ route('admin.reports.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.reports.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-chart-area text-base"></i></span>
                    <span>Reports</span>
                </a>
            </li>

            <li class="pt-3"><p class="sidebar-section-label px-3 mb-1">System</p></li>
            @if (Route::has('admin.files.index'))
            <li>
                <a href="{{ route('admin.files.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.files.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-folder-open text-base"></i></span>
                    <span>Files</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.billing.index'))
            <li>
                <a href="{{ route('admin.billing.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.billing.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-credit-card text-base"></i></span>
                    <span>Billing</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.notifications.index'))
            <li>
                <a href="{{ route('admin.notifications.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.notifications.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                    <span>Notifications</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.webhooks.index'))
            <li>
                <a href="{{ route('admin.webhooks.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.webhooks.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-plug text-base"></i></span>
                    <span>Webhooks</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.audit.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.audit.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-history text-base"></i></span>
                    <span>Audit Logs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="admin-nav-link nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                    <span>Settings</span>
                </a>
            </li>
        @endif

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
<nav class="hidden lg:flex lg:fixed lg:top-[132px] lg:left-0 lg:h-[calc(100vh-132px)] lg:w-64 sidebar-bg lg:text-white lg:flex-col lg:overflow-y-auto sidebar-scroll z-40">

    <ul class="mt-2 space-y-0.5 px-3 flex-1">

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-home text-base"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.plans.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.plans.*') ? 'nav-active' : 'nav-default' }}">
                <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                <span>Plans</span>
            </a>
        </li>

        @if (Auth::user()->tenant_id === null)
            {{-- Platform Admin --}}
            <li class="pt-4"><p class="sidebar-section-label px-3 mb-1">Platform</p></li>
            <li>
                <a href="{{ route('admin.tenants.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.tenants.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-building text-base"></i></span>
                    <span>Subscribers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.users.*','admin.roles.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                    <span>Users &amp; Roles</span>
                </a>
            </li>
            @if (Route::has('admin.notifications.index'))
            <li>
                <a href="{{ route('admin.notifications.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.notifications.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                    <span>Notifications</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                    <span>Settings</span>
                </a>
            </li>

        @else
            {{-- Tenant Context --}}
            <li class="pt-4"><p class="sidebar-section-label px-3 mb-1">Operations</p></li>
            @if (Route::has('admin.projects.index'))
            <li>
                <a href="{{ route('admin.projects.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.projects.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-layer-group text-base"></i></span>
                    <span>Projects</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.forms.index'))
            <li>
                <a href="{{ route('admin.forms.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.forms.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-wpforms text-base"></i></span>
                    <span>Forms</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.categories.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-tags text-base"></i></span>
                    <span>Categories</span>
                </a>
            </li>
            @if (Route::has('admin.work-orders.index'))
            <li>
                <a href="{{ route('admin.work-orders.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.work-orders.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-tools text-base"></i></span>
                    <span>Work Orders</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.records.index'))
            <li>
                <a href="{{ route('admin.records.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.records.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-table text-base"></i></span>
                    <span>Records</span>
                </a>
            </li>
            @endif

            <li class="pt-4"><p class="sidebar-section-label px-3 mb-1">People</p></li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-users text-base"></i></span>
                    <span>Users &amp; Roles</span>
                </a>
            </li>

            <li class="pt-4"><p class="sidebar-section-label px-3 mb-1">Insights</p></li>
            <li>
                <a href="{{ route('admin.reports.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.reports.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-chart-area text-base"></i></span>
                    <span>Reports</span>
                </a>
            </li>

            <li class="pt-4"><p class="sidebar-section-label px-3 mb-1">System</p></li>
            @if (Route::has('admin.files.index'))
            <li>
                <a href="{{ route('admin.files.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.files.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-folder-open text-base"></i></span>
                    <span>Files</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.billing.index'))
            <li>
                <a href="{{ route('admin.billing.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.billing.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-credit-card text-base"></i></span>
                    <span>Billing</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.notifications.index'))
            <li>
                <a href="{{ route('admin.notifications.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.notifications.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-bell text-base"></i></span>
                    <span>Notifications</span>
                </a>
            </li>
            @endif
            @if (Route::has('admin.webhooks.index'))
            <li>
                <a href="{{ route('admin.webhooks.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.webhooks.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-plug text-base"></i></span>
                    <span>Webhooks</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('admin.audit.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.audit.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-history text-base"></i></span>
                    <span>Audit Logs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.settings.*') ? 'nav-active' : 'nav-default' }}">
                    <span class="nav-icon-wrap"><i class="fas fa-sliders-h text-base"></i></span>
                    <span>Settings</span>
                </a>
            </li>
        @endif

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
.sidebar-bg { background: linear-gradient(180deg, #005f8e 0%, #004a72 50%, #003a5c 100%); }
.sidebar-section-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.40); }
.nav-default { color: rgba(255,255,255,0.72); }
.nav-default:hover { background: rgba(255,255,255,0.10); color: #ffffff; }
.nav-active { background: rgba(255,255,255,0.18); color: #ffffff; box-shadow: inset 3px 0 0 #7dd3fc; }
.nav-icon-wrap { display:flex; align-items:center; justify-content:center; width:1.75rem; height:1.75rem; border-radius:0.5rem; background:rgba(255,255,255,0.08); flex-shrink:0; font-size:0.8rem; }
.nav-active .nav-icon-wrap { background: rgba(125,211,252,0.25); color: #7dd3fc; }
.nav-default:hover .nav-icon-wrap { background: rgba(255,255,255,0.15); }
.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
.sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebar      = document.getElementById('adminMobileSidebar');
    var sidebarToggle  = document.getElementById('adminMobileSidebarToggle');
    var sidebarClose   = document.getElementById('adminMobileSidebarClose');
    var sidebarOverlay = document.getElementById('adminMobileSidebarOverlay');
    function openSidebar()  { if (sidebar) sidebar.classList.remove('-translate-x-full'); if (sidebarOverlay) sidebarOverlay.classList.remove('hidden'); }
    function closeSidebar() { if (sidebar) sidebar.classList.add('-translate-x-full');    if (sidebarOverlay) sidebarOverlay.classList.add('hidden'); }
    if (sidebarToggle)  sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose)   sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.admin-nav-link').forEach(function(link) { link.addEventListener('click', closeSidebar); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });
});
</script>
