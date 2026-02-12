@extends('tenant.layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
      <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold">Create New Project</h2>
            <p class="text-blue-100 mt-1">Define project details, schedule, area, client and team</p>
          </div>
          <a href="{{ route('tenant.projects.index') }}" class="text-blue-100 hover:text-white transition">Back to Projects</a>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6">
        @if ($errors->any())
          <div class="mb-6 rounded border border-red-300 bg-red-50 p-4 text-red-700">
            <div class="font-semibold mb-2">Please fix the following:</div>
            <ul class="list-disc list-inside text-sm">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">What is a project?</h3>
              <p class="mt-1 text-sm text-blue-700">
                Projects group work by site or job. New projects start as <strong>Draft</strong>. When you set status to <strong>Active</strong>, the project becomes visible for work orders, form assignments, and team assignments; activate only when you are ready to use it.
              </p>
            </div>
          </div>
        </div>

        <form method="POST" action="{{ route('tenant.projects.store') }}" id="projectForm" class="space-y-8">
          @csrf

          <!-- Basic Information -->
          <section class="border-l-4 border-blue-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Enter project name">
              </div>

              <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Project Code</label>
                <input id="code" name="code" type="text" value="{{ old('code') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Leave blank to auto-generate (e.g. PROJECT-1-26-02-12-001-143052)">
                <p class="mt-1 text-xs text-gray-500">Optional. Auto-generated from name + date + time if blank.</p>
              </div>

              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required
                        class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="3" {{ old('status','3')=='3' ? 'selected' : '' }}>Draft</option>
                  <option value="1" {{ old('status')=='1' ? 'selected' : '' }}>Active</option>
                  <option value="2" {{ old('status')=='2' ? 'selected' : '' }}>Paused</option>
                  <option value="0" {{ old('status')=='0' ? 'selected' : '' }}>Archived</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Describe your project...">{{ old('description') }}</textarea>
              </div>
            </div>
          </section>

          <!-- Schedule -->
          <section class="border-l-4 border-green-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
            </div>
          </section>

          <!-- Area and Client -->
          <section class="border-l-4 border-amber-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Client & Area</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                <input id="client_name" name="client_name" type="text" value="{{ old('client_name') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Client / Customer name">
              </div>
              <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-2">Area</label>
                <input id="area" name="area" type="text" value="{{ old('area') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="e.g., Downtown, Zone A, Sector 5">
              </div>
            </div>
          </section>

          <!-- Team -->
          <section class="border-l-4 border-indigo-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Membership Assignment</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Managers</label>
                <p class="text-xs text-gray-500 mb-2">Oversees the project and assigns work orders to field users.</p>
                <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50">
                  @forelse($users as $user)
                    <label class="flex items-center py-2 px-2 hover:bg-white rounded cursor-pointer">
                      <input type="checkbox" name="managers[]" value="{{ $user->id }}" {{ in_array($user->id, old('managers', [])) ? 'checked' : '' }}
                             class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                      <span class="ml-2 text-sm text-gray-900">{{ $user->name }}</span>
                    </label>
                  @empty
                    <p class="text-sm text-gray-500 text-center py-4">No users available</p>
                  @endforelse
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Field Users</label>
                <p class="text-xs text-gray-500 mb-2">Completes assigned work orders and submits records in the field.</p>
                <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50">
                  @forelse($users as $user)
                    <label class="flex items-center py-2 px-2 hover:bg-white rounded cursor-pointer">
                      <input type="checkbox" name="field_users[]" value="{{ $user->id }}" {{ in_array($user->id, old('field_users', [])) ? 'checked' : '' }}
                             class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                      <span class="ml-2 text-sm text-gray-900">{{ $user->name }}</span>
                    </label>
                  @empty
                    <p class="text-sm text-gray-500 text-center py-4">No users available</p>
                  @endforelse
                </div>
              </div>
            </div>
          </section>

          <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 p-4 rounded-r-lg">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Next step: set up your project</h3>
                <p class="mt-1 text-sm text-green-700">After creating this project, you can:</p>
                <ul class="mt-2 text-sm text-green-700 list-disc list-inside space-y-1">
                  <li>Open the project page to add work orders and assign forms</li>
                  <li>Assign or change managers and field users from the project</li>
                  <li>Track work orders and records from the project dashboard</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="{{ route('tenant.projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">Create Project</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

