@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-700 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Add New Category</h1>
                        <p class="mt-1 text-sm text-blue-100">
                            Group your forms into meaningful categories for better organization.
                        </p>
                    </div>
                    <a href="{{ route('admin.categories.index') }}"
                       class="inline-flex items-center text-sm text-blue-100 hover:text-white transition">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Categories
                    </a>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-6">
                    <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-8">
                        @csrf

                        @include('admin.category._form')

                        {{-- Buttons --}}
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.categories.index') }}"
                               class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200
                                      text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold
                                           text-white bg-blue-600 hover:bg-blue-700 shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Save Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
