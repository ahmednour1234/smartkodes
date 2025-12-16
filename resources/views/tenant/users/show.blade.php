@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">User Profile</h2>
                            <p class="text-blue-100 mt-1">{{ $user->name }}'s account details</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('tenant.users.edit', $user) }}"
                               class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Edit User
                            </a>
                            <a href="{{ route('tenant.users.index') }}"
                               class="bg-gray-100 text-gray-700 hover:bg-gray-200 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Back to Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Avatar -->
                            <div class="text-center mb-6">
                                <div class="h-24 w-24 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-3xl mx-auto mb-4">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-gray-600">{{ $user->email }}</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Status:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($user->status == 1) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Role:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($user->role == 'admin') bg-purple-100 text-purple-800
                                        @elseif($user->role == 'manager') bg-blue-100 text-blue-800
                                        @elseif($user->role == 'field_worker') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Joined:</span>
                                    <span class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="space-y-2">
                                <a href="mailto:{{ $user->email }}"
                                   class="w-full bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded text-sm font-medium text-center block transition duration-200">
                                    Send Email
                                </a>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}"
                                       class="w-full bg-green-50 text-green-700 hover:bg-green-100 px-3 py-2 rounded text-sm font-medium text-center block transition duration-200">
                                        Call {{ $user->phone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="lg:col-span-2">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?: 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($user->status == 1) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Activity Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Summary</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $user->projects_count ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Projects Assigned</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $user->work_orders_count ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Work Orders</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $user->submissions_count ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Form Submissions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                            @if(isset($recentActivities) && $recentActivities->count() > 0)
                                <div class="space-y-4">
                                    @foreach($recentActivities as $activity)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                                <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-4">No recent activity found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
