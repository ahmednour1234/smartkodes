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
<main class=" md:ml-64">
    @yield('content')
</main>

        </div>

        @if(!session('onboarding_done'))
        <div id="onboarding-backdrop" class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
            <div id="onboarding-modal" class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div id="onboarding-step-1" class="onboarding-step p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Welcome to Smart Kodes</h2>
                    <p class="text-gray-600 text-sm mb-6">Your workspace for projects, forms, work orders, and team collaboration. This short tour highlights the main areas.</p>
                    <button type="button" onclick="onboardingNext()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg">Next</button>
                </div>
                <div id="onboarding-step-2" class="onboarding-step p-6 hidden">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Key sections</h2>
                    <ul class="text-gray-600 text-sm space-y-2 mb-6">
                        <li><strong>Dashboard</strong> — Overview and quick stats</li>
                        <li><strong>Operations</strong> — Projects, Forms, Work Orders, Records</li>
                        <li><strong>People</strong> — Team members and roles</li>
                        <li><strong>System</strong> — Files, Billing, Notifications, Settings</li>
                    </ul>
                    <button type="button" onclick="onboardingNext()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg">Next</button>
                </div>
                <div id="onboarding-step-3" class="onboarding-step p-6 hidden">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Permissions</h2>
                    <p class="text-gray-600 text-sm mb-4">We may ask for:</p>
                    <ul class="text-gray-600 text-sm space-y-2 mb-6">
                        <li><strong>Notifications</strong> — To alert you about new assignments and updates</li>
                        <li><strong>Location</strong> — Only when a form or work order requires it (e.g. field checks)</li>
                    </ul>
                    <p class="text-xs text-gray-500 mb-6">You can change these later in your device or browser settings.</p>
                    <button type="button" onclick="onboardingComplete()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg">Get started</button>
                </div>
            </div>
        </div>
        <script>
        var onboardingStep = 1;
        function onboardingNext() {
            document.getElementById('onboarding-step-' + onboardingStep).classList.add('hidden');
            onboardingStep++;
            document.getElementById('onboarding-step-' + onboardingStep).classList.remove('hidden');
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
