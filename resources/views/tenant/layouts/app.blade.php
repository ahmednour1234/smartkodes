<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Tenant Portal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Chart.js for charts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Leaflet CSS for maps -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>

        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>

        <!-- Scripts -->
        <script src="{{ asset('build/assets/app-CXDpL9bK.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('tenant.layouts.sidebar')

            <!-- Page Heading -->
        <!-- Page Heading -->
@isset($header)
    <header class="bg-white shadow  md:pt-0 md:ml-64">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
@endisset

<!-- Page Content -->
<main class="pt-14 md:pt-0 md:ml-64">
    @yield('content')
</main>

        </div>

        @if(auth()->user() && !auth()->user()->onboarding_completed_at)
        <div id="onboarding-backdrop" class="fixed inset-0 bg-black/70 backdrop-blur-xl z-[60] flex items-center justify-center p-6">
            <div id="onboarding-modal" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100 ring-1 ring-black/5">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 px-8 py-6 rounded-t-2xl">
                    <div class="flex gap-2 mb-4">
                        <span class="w-3 h-3 rounded-full bg-white/90" id="dot-1"></span>
                        <span class="w-3 h-3 rounded-full bg-white/40 transition-colors" id="dot-2"></span>
                        <span class="w-3 h-3 rounded-full bg-white/40 transition-colors" id="dot-3"></span>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-1" id="onboarding-title">Welcome to Smart Kodes</h2>
                    <p class="text-blue-100 text-sm" id="onboarding-subtitle">Your workspace for projects, forms, work orders, and team collaboration.</p>
                </div>
                <div id="onboarding-step-1" class="onboarding-step p-8">
                    <p class="text-gray-600 text-base leading-relaxed mb-8">This short tour highlights the main areas so you can get started quickly.</p>
                    <button type="button" onclick="onboardingNext()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-blue-500/25 transition-all">Next</button>
                </div>
                <div id="onboarding-step-2" class="onboarding-step p-8 hidden">
                    <ul class="text-gray-600 text-base space-y-4 mb-8">
                        <li class="flex items-start gap-3"><span class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-semibold text-sm">1</span><span><strong class="text-gray-900">Dashboard</strong> — Overview and quick stats</span></li>
                        <li class="flex items-start gap-3"><span class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold text-sm">2</span><span><strong class="text-gray-900">Operations</strong> — Projects, Forms, Work Orders, Records</span></li>
                        <li class="flex items-start gap-3"><span class="flex-shrink-0 w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center font-semibold text-sm">3</span><span><strong class="text-gray-900">People</strong> — Team members and roles</span></li>
                        <li class="flex items-start gap-3"><span class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-semibold text-sm">4</span><span><strong class="text-gray-900">System</strong> — Files, Billing, Notifications, Settings</span></li>
                    </ul>
                    <button type="button" onclick="onboardingNext()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-blue-500/25 transition-all">Next</button>
                </div>
                <div id="onboarding-step-3" class="onboarding-step p-8 hidden">
                    <p class="text-gray-600 text-base mb-4">We may ask for:</p>
                    <ul class="text-gray-600 text-base space-y-3 mb-6">
                        <li class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-blue-500"></span><strong>Notifications</strong> — To alert you about new assignments and updates</li>
                        <li class="flex items-center gap-3"><span class="w-2 h-2 rounded-full bg-indigo-500"></span><strong>Location</strong> — Only when a form or work order requires it (e.g. field checks)</li>
                    </ul>
                    <p class="text-sm text-gray-500 mb-8">You can change these later in your device or browser settings.</p>
                    <button type="button" onclick="onboardingComplete()" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-emerald-500/25 transition-all">Get started</button>
                </div>
            </div>
        </div>
        <script>
        var onboardingStep = 1;
        var titles = ['Welcome to Smart Kodes', 'Key sections', 'Permissions'];
        var subtitles = ['Your workspace for projects, forms, work orders, and team collaboration.', 'Navigate easily between main areas.', 'We may ask for these when needed.'];
        function onboardingNext() {
            document.getElementById('onboarding-step-' + onboardingStep).classList.add('hidden');
            onboardingStep++;
            document.getElementById('onboarding-step-' + onboardingStep).classList.remove('hidden');
            document.getElementById('onboarding-title').textContent = titles[onboardingStep - 1];
            document.getElementById('onboarding-subtitle').textContent = subtitles[onboardingStep - 1];
            document.querySelectorAll('[id^="dot-"]').forEach(function(d, i) { d.className = 'w-3 h-3 rounded-full transition-colors ' + (i + 1 === onboardingStep ? 'bg-white/90' : 'bg-white/40'); });
        }
        function onboardingComplete() {
            fetch('{{ route("tenant.onboarding.complete") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(function() {
                document.getElementById('onboarding-backdrop').remove();
            });
        }
        </script>
        @endif

        @stack('scripts')
    </body>
</html>
