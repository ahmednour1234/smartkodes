@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-visible shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white overflow-visible">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Forms</h2>
                            <p class="text-blue-100 mt-1">Manage your organization's digital forms</p>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:gap-3 items-center">
                            <!-- Export Dropdown -->
                            <div class="relative inline-block text-left">
                                <button type="button" onclick="toggleFormExportMenu()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="form-export-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                    <div class="py-1" role="menu">
                                        <a href="{{ route('tenant.forms.export-list', ['format' => 'xlsx'] + request()->only('category_id')) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <svg class="w-4 h-4 inline mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Excel (.xlsx)
                                        </a>
                                        <a href="{{ route('tenant.forms.export-list', ['format' => 'csv'] + request()->only('category_id')) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <svg class="w-4 h-4 inline mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!--<a href="{{ route('tenant.forms.templates') }}"-->
                            <!--   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition duration-200">-->
                            <!--    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
                            <!--        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />-->
                            <!--    </svg>-->
                            <!--    Templates-->
                            <!--</a>-->
                            <a href="{{ route('tenant.forms.create') }}"
                               class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center whitespace-nowrap shrink-0">
                                <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Form
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('tenant.forms.index') }}" class="flex flex-wrap items-end gap-4">
                        <div class="w-full md:w-1/3">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Category
                            </label>
                            <select name="category_id" id="category_id"
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ (string)request('category_id') === (string)$category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                Apply Filter
                            </button>

                            @if(request('category_id'))
                                <a href="{{ route('tenant.forms.index') }}"
                                   class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Clear filters
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Forms Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @forelse($forms as $form)
                    <div class="bg-white overflow-visible shadow-sm sm:rounded-lg hover:shadow-md transition duration-200">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $form->name }}</h3>
                                    <p class="text-gray-600 text-sm mb-3">{{ Str::limit($form->description, 100) }}</p>

                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        {{-- Status badge --}}
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($form->status == 0) bg-gray-100 text-gray-800
                                            @elseif($form->status == 1) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            @if($form->status == 0) Draft
                                            @elseif($form->status == 1) Active
                                            @else Inactive @endif
                                        </span>

                                        {{-- Category badge --}}
                                        @if($form->category)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700">
                                                {{ $form->category->name }}
                                            </span>
                                        @endif

                                        <span class="group relative inline-flex items-center gap-0.5 cursor-help">
                                        <span class="text-xs text-gray-500">v{{ $form->version }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="absolute bottom-full left-0 mb-1 px-2 py-1.5 text-xs font-normal text-white bg-gray-800 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity duration-150 z-50 w-56 text-left normal-case">Version increases when you publish in Builder; work orders keep their assigned version.</span>
                                    </span>
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        Used in {{ $form->workOrders->count() }} work order{{ $form->workOrders->count() !== 1 ? 's' : '' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('tenant.forms.builder', $form) }}"
                                   class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold text-center transition duration-200 shadow-sm">
                                    Builder
                                </a>
                                <div class="relative inline-block text-left">
                                    <button type="button" onclick="toggleFormActionsMenu('form-actions-{{ $form->id }}')"
                                            class="inline-flex items-center justify-center w-10 h-[42px] border border-gray-300 rounded-lg bg-white text-gray-600 hover:bg-gray-50 transition duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div id="form-actions-{{ $form->id }}" class="hidden origin-top-right absolute right-0 mt-1 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                        <div class="py-1" role="menu">
                                            <a href="{{ route('tenant.forms.show', $form) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">View</a>
                                            <a href="{{ route('tenant.forms.edit', $form) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Edit</a>
                                            <a href="{{ route('tenant.records.index', ['form_id' => $form->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Submissions</a>
                                            <a href="{{ route('tenant.forms.clone', $form) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Copy</a>
                                            <div class="border-t border-gray-100" role="separator"></div>
                                            <form action="{{ route('tenant.forms.destroy', $form) }}" method="POST" class="block" role="menuitem"
                                                  onsubmit="return confirm('Are you sure you want to delete this form? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No forms yet</h3>
                                <p class="text-gray-500 mb-6">Create your first form to start collecting data from your field teams.</p>
                                <div class="flex justify-center space-x-4">
                                    <a href="{{ route('tenant.forms.templates') }}"
                                       class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                        Browse Templates
                                    </a>
                                    <a href="{{ route('tenant.forms.create') }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                        Create Form
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($forms->hasPages())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        {{ $forms->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleFormExportMenu() {
            const menu = document.getElementById('form-export-menu');
            menu.classList.toggle('hidden');
        }

        function toggleFormActionsMenu(id) {
            const menu = document.getElementById(id);
            document.querySelectorAll('[id^="form-actions-"]').forEach(function(m) {
                if (m.id !== id) m.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('form-export-menu');
            const button = event.target.closest('button[onclick="toggleFormExportMenu()"]');
            if (!button && menu && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
            if (!event.target.closest('[id^="form-actions-"]') && !event.target.closest('button[onclick^="toggleFormActionsMenu"]')) {
                document.querySelectorAll('[id^="form-actions-"]').forEach(function(m) { m.classList.add('hidden'); });
            }
        });
    </script>
@endsection
