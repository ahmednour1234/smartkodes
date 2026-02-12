@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Edit Project</h2>
                            <p class="text-blue-100 mt-1">{{ $project->name }}</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('tenant.projects.show', $project) }}"
                               class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                View Project
                            </a>
                            <a href="{{ route('tenant.projects.index') }}"
                               class="text-blue-100 hover:text-white transition duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Projects
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <form action="{{ route('tenant.projects.update', $project) }}" method="POST" id="projectForm">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="space-y-6">
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Project Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Enter project name">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Project Code <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="code" id="code" value="{{ old('code', $project->code) }}" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="e.g., PROJ-001">
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Unique identifier for this project</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea name="description" id="description" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Enter project description">{{ old('description', $project->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" id="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="3" {{ old('status', $project->status) == 3 ? 'selected' : '' }}>Draft</option>
                                        <option value="1" {{ old('status', $project->status) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="2" {{ old('status', $project->status) == 2 ? 'selected' : '' }}>Paused</option>
                                        <option value="0" {{ old('status', $project->status) == 0 ? 'selected' : '' }}>Archived</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Schedule (Optional) -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule (Optional)</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Start Date
                                    </label>
                                    <input type="date" name="start_date" id="start_date"
                                        value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        End Date
                                    </label>
                                    <input type="date" name="end_date" id="end_date"
                                        value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Client & Area (Optional) -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client & Area (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Client Name
                                    </label>
                                    <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $project->client_name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Enter client name">
                                    @error('client_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="area" class="block text-sm font-medium text-gray-700 mb-2">
                                        Area
                                    </label>
                                    <input type="text" name="area" id="area" value="{{ old('area', $project->area) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="e.g., Downtown, Zone A">
                                    @error('area')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Team Assignment -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Assignment</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Managers</label>
                                    <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto bg-white">
                                        @forelse($users as $user)
                                            <label class="flex items-center py-1 hover:bg-gray-50">
                                                <input type="checkbox" name="managers[]" value="{{ $user->id }}"
                                                    {{ in_array($user->id, old('managers', $managers->pluck('id')->toArray())) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-indigo-600">
                                                <span class="ml-2 text-sm">{{ $user->name }}</span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500">No users available</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Field Users</label>
                                    <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto bg-white">
                                        @forelse($users as $user)
                                            <label class="flex items-center py-1 hover:bg-gray-50">
                                                <input type="checkbox" name="field_users[]" value="{{ $user->id }}"
                                                    {{ in_array($user->id, old('field_users', $fieldUsers->pluck('id')->toArray())) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-indigo-600">
                                                <span class="ml-2 text-sm">{{ $user->name }}</span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500">No users available</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('tenant.projects.show', $project) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                Update Project
                            </button>
                        </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
