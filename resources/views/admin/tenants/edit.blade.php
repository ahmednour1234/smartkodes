@extends('admin.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Tenant: {{ $tenant->name }}</h2>
                        <a href="{{ route('admin.tenants.index') }}" class="text-gray-600 hover:text-gray-900">← Back to Tenants</a>
                    </div>

                    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Tenant Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="domain" class="block text-sm font-medium text-gray-700">Domain (Optional)</label>
                                <input type="text" name="domain" id="domain" value="{{ old('domain', $tenant->domain) }}" placeholder="tenant.example.com" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('domain')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="plan_id" class="block text-sm font-medium text-gray-700">Plan</label>
                                <select name="plan_id" id="plan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Select a plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id', $tenant->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} - ${{ $plan->price }}/month
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="0" {{ old('status', $tenant->status) == '0' ? 'selected' : '' }}>Draft</option>
                                    <option value="1" {{ old('status', $tenant->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ old('status', $tenant->status) == '2' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="storage_quota" class="block text-sm font-medium text-gray-700">Storage Quota (MB)</label>
                                <input type="number" name="storage_quota" id="storage_quota" value="{{ old('storage_quota', $tenant->storage_quota) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" min="0">
                                @error('storage_quota')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="api_rate_limit" class="block text-sm font-medium text-gray-700">API Rate Limit (requests/hour)</label>
                                <input type="number" name="api_rate_limit" id="api_rate_limit" value="{{ old('api_rate_limit', $tenant->api_rate_limit) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" min="0">
                                @error('api_rate_limit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete Tenant
                                </button>
                            </form>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
