@extends('admin.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h2>
                            <p class="text-gray-600">{{ $tenant->domain ?: 'No custom domain' }}</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.tenants.edit', $tenant) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                            <form method="POST" action="{{ route('admin.tenants.impersonate', $tenant) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Impersonate</button>
                            </form>
                            <a href="{{ route('admin.tenants.index') }}" class="text-gray-600 hover:text-gray-900">← Back to Tenants</a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Status Card -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Status</h3>
                            <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                @if($tenant->status == 0) bg-gray-100 text-gray-800
                                @elseif($tenant->status == 1) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($tenant->status == 0) Draft
                                @elseif($tenant->status == 1) Active
                                @else Suspended @endif
                            </span>
                        </div>

                        <!-- Plan Card -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Plan</h3>
                            <p class="text-gray-600">{{ $tenant->plan->name ?? 'No Plan' }}</p>
                            <p class="text-sm text-gray-500">${{ $tenant->plan->price ?? 0 }}/month</p>
                        </div>

                        <!-- Users Card -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Users</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ $tenant->users->count() }}</p>
                            <p class="text-sm text-gray-500">Active users</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Tenant Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tenant Details</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $tenant->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Slug</dt>
                                    <dd class="text-sm text-gray-900">{{ $tenant->slug }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Domain</dt>
                                    <dd class="text-sm text-gray-900">{{ $tenant->domain ?: 'Not configured' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Storage Quota</dt>
                                    <dd class="text-sm text-gray-900">{{ number_format($tenant->storage_quota) }} MB</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">API Rate Limit</dt>
                                    <dd class="text-sm text-gray-900">{{ number_format($tenant->api_rate_limit) }} requests/hour</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="text-sm text-gray-900">{{ $tenant->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($tenant->updated_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="text-sm text-gray-900">{{ $tenant->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Health & Usage -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Health & Usage</h3>
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded">
                                    <h4 class="font-medium text-blue-900">Projects</h4>
                                    <p class="text-blue-700">{{ $tenant->projects->count() }} projects</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded">
                                    <h4 class="font-medium text-green-900">Storage Used</h4>
                                    <p class="text-green-700">~{{ rand(10, 500) }} MB used</p> <!-- Placeholder -->
                                </div>
                                <div class="bg-yellow-50 p-4 rounded">
                                    <h4 class="font-medium text-yellow-900">API Usage</h4>
                                    <p class="text-yellow-700">{{ rand(100, 5000) }} requests today</p> <!-- Placeholder -->
                                </div>
                                <div class="bg-red-50 p-4 rounded">
                                    <h4 class="font-medium text-red-900">Error Rate</h4>
                                    <p class="text-red-700">{{ rand(0, 5) }}% error rate</p> <!-- Placeholder -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Users -->
                    @if($tenant->users->count() > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Users</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tenant->users->take(5) as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->roles->first()->name ?? 'No Role' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->created_at->format('M d, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
