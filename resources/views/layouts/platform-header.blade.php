@php
    $currentUser = Auth::user();
    $sessionTenant = session('tenant_context.current_tenant');
    $currentTenant = $sessionTenant ? \App\Models\Tenant::find($sessionTenant->id) ?? $sessionTenant : null;

    $displayName = $currentUser?->name ?? 'User';
    $displayEmail = $currentUser?->email ?? '';
    $companyName = $currentTenant?->name ?? 'Platform Admin';
    $helpNumber = config('services.whatsapp.help_number', '201234567890');
    $isAdminRoute = request()->routeIs('admin.*');
    $toggleId = $isAdminRoute ? 'adminMobileSidebarToggle' : 'mobileSidebarToggle';
    $toggleVisibilityClass = $isAdminRoute ? 'lg:hidden' : 'md:hidden';
@endphp

<div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="md:hidden px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between gap-2 mb-2">
            <button id="{{ $toggleId }}" type="button" class="inline-flex {{ $toggleVisibilityClass }} items-center justify-center rounded-md p-2 border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex items-center justify-end gap-3 flex-1">
                <a href="https://wa.me/{{ $helpNumber }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-md border border-gray-200 bg-white text-gray-700 text-xs font-medium hover:bg-gray-50">
                    <svg class="w-4 h-4 text-green-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.56 2 2.1 6.45 2.1 11.94c0 1.76.46 3.49 1.33 5.01L2 22l5.2-1.36a9.9 9.9 0 0 0 4.83 1.23h.01c5.48 0 9.94-4.45 9.95-9.94A9.93 9.93 0 0 0 12.04 2Zm5.79 14.09c-.24.67-1.42 1.29-1.96 1.38-.5.09-1.14.13-1.83-.09-.42-.13-.96-.31-1.65-.61-2.9-1.25-4.79-4.17-4.94-4.37-.15-.2-1.18-1.56-1.18-2.97 0-1.41.74-2.1 1-2.39.26-.29.57-.36.76-.36h.55c.18 0 .43-.07.67.5.24.58.81 1.99.88 2.13.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.59.16.29.72 1.19 1.54 1.93 1.06.94 1.96 1.23 2.25 1.37.29.14.46.12.63-.07.17-.19.74-.86.94-1.16.2-.3.4-.25.67-.15.27.1 1.72.81 2.01.95.29.15.48.22.55.34.06.12.06.69-.18 1.36Z"></path></svg>
                    <span>Get Help</span>
                </a>

                <details class="relative">
                    <summary class="list-none cursor-pointer inline-flex items-center gap-2 pl-2 pr-2.5 py-1 rounded-md border border-gray-200 bg-white hover:bg-gray-50">
                        @if($currentUser?->photo_url)
                            <img src="{{ $currentUser->photo_url }}" alt="{{ $displayName }}" class="h-7 w-7 rounded-full object-cover border border-gray-200">
                        @else
                            <div class="h-7 w-7 rounded-full border border-gray-200 bg-gray-100 text-gray-700 text-xs font-semibold flex items-center justify-center">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                        @endif
                        <div class="text-left hidden sm:block">
                            <p class="text-xs font-semibold text-gray-900 leading-tight">{{ $displayName }}</p>
                            <p class="text-[11px] text-gray-500 leading-tight">{{ $displayEmail }}</p>
                        </div>
                    </summary>
                </details>
            </div>
        </div>
        <div class="text-center">
            <h1 class="text-2xl font-extrabold tracking-wide text-cyan-700">Welcome to SMART SITE</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Work Orders and Site Reporting in One Platform</p>
        </div>
    </div>

    <div class="hidden md:flex">
        <div class="w-64 p-3 space-y-2 bg-white border-r border-gray-200">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/NewIcon.png') }}" alt="Smart Site" class="h-8 w-8 rounded-full object-cover border border-gray-200 bg-white">
                <p class="font-semibold text-gray-900">Smart Site</p>
            </div>
            <div class="flex items-center gap-3">
                @if($currentTenant?->logo_url)
                    <img src="{{ $currentTenant->logo_url }}" alt="{{ $companyName }} logo" class="h-7 w-7 rounded-full object-cover border border-gray-200 bg-white">
                @else
                    <div class="h-7 w-7 rounded-full bg-gray-100 border border-gray-200 text-gray-700 flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
                @endif
                <p class="text-sm text-gray-700 truncate">{{ $companyName }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($currentUser?->photo_url)
                    <img src="{{ $currentUser->photo_url }}" alt="{{ $displayName }}" class="h-7 w-7 rounded-full object-cover border border-gray-200 bg-white">
                @else
                    <div class="h-7 w-7 rounded-full bg-gray-100 border border-gray-200 text-gray-700 flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                @endif
                <p class="text-sm text-gray-700 truncate">{{ $displayName }}</p>
            </div>
        </div>

        <div class="flex-1">
            <div class="max-w-7xl mx-auto px-5 lg:px-7 py-3">
                <div class="flex items-center justify-end gap-2 mb-2">
                    <a href="https://wa.me/{{ $helpNumber }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-md border border-gray-200 bg-white text-gray-700 text-xs font-medium hover:bg-gray-50">
                        <svg class="w-4 h-4 text-green-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.56 2 2.1 6.45 2.1 11.94c0 1.76.46 3.49 1.33 5.01L2 22l5.2-1.36a9.9 9.9 0 0 0 4.83 1.23h.01c5.48 0 9.94-4.45 9.95-9.94A9.93 9.93 0 0 0 12.04 2Zm5.79 14.09c-.24.67-1.42 1.29-1.96 1.38-.5.09-1.14.13-1.83-.09-.42-.13-.96-.31-1.65-.61-2.9-1.25-4.79-4.17-4.94-4.37-.15-.2-1.18-1.56-1.18-2.97 0-1.41.74-2.1 1-2.39.26-.29.57-.36.76-.36h.55c.18 0 .43-.07.67.5.24.58.81 1.99.88 2.13.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.59.16.29.72 1.19 1.54 1.93 1.06.94 1.96 1.23 2.25 1.37.29.14.46.12.63-.07.17-.19.74-.86.94-1.16.2-.3.4-.25.67-.15.27.1 1.72.81 2.01.95.29.15.48.22.55.34.06.12.06.69-.18 1.36Z"></path></svg>
                        <span>Get Help</span>
                    </a>

                    <details class="relative">
                        <summary class="list-none cursor-pointer inline-flex items-center gap-2 pl-2 pr-2.5 py-1 rounded-md border border-gray-200 bg-white hover:bg-gray-50">
                            @if($currentUser?->photo_url)
                                <img src="{{ $currentUser->photo_url }}" alt="{{ $displayName }}" class="h-7 w-7 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="h-7 w-7 rounded-full border border-gray-200 bg-gray-100 text-gray-700 text-xs font-semibold flex items-center justify-center">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                            @endif
                            <div class="text-left hidden sm:block">
                                <p class="text-xs font-semibold text-gray-900 leading-tight">{{ $displayName }}</p>
                                <p class="text-[11px] text-gray-500 leading-tight">{{ $displayEmail }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>

                        <div class="absolute right-0 mt-2 w-56 rounded-md border border-gray-200 bg-white shadow-lg z-20">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $displayName }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $displayEmail }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="p-2">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-sm rounded-md text-red-600 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </details>
                </div>

                <div class="text-center">
                    <h1 class="text-2xl font-extrabold tracking-wide text-cyan-700">Welcome to SMART SITE</h1>
                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Work Orders and Site Reporting in One Platform</p>
                </div>
            </div>
        </div>
    </div>
</div>
