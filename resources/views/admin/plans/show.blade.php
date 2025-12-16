@extends('admin.layouts.app')

@section('content')    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Plan Details</h2>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                Edit Plan
                            </a>
                            <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Back to Plans
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Basic Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                <div>
                                    <span class="text-sm text-gray-500">Name:</span>
                                    <p class="text-gray-900 font-medium">{{ $plan->name }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Slug:</span>
                                    <p class="text-gray-900">{{ $plan->slug }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Price:</span>
                                    <p class="text-gray-900 font-bold text-lg">${{ number_format($plan->price, 2) }}/month</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Status:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $plan->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Features</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @if(is_array($plan->features) && count($plan->features) > 0)
                                    <ul class="space-y-2">
                                        @foreach($plan->features as $key => $value)
                                            <li class="flex justify-between items-center">
                                                <span class="text-gray-600">{{ is_numeric($key) ? ($value['key'] ?? 'Feature') : $key }}:</span>
                                                <span class="font-medium text-gray-900">{{ is_array($value) ? ($value['value'] ?? '') : $value }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-gray-500">No features defined</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($plan->description)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Description</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700">{{ $plan->description }}</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Subscriptions</h3>
                        @if($plan->subscriptions && $plan->subscriptions->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($plan->subscriptions as $subscription)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ route('admin.tenants.show', $subscription->tenant) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        {{ $subscription->tenant->name }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $subscription->start_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $subscription->end_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $subscription->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-gray-50 p-4 rounded-lg text-center text-gray-500">
                                No subscriptions for this plan yet
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection