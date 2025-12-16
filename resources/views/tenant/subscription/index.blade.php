<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Subscription -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Current Subscription</h2>

                    @if($tenant->subscription)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="col-span-2">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-2xl font-bold">{{ $tenant->subscription->plan->name }}</h3>
                                            <p class="text-indigo-100 mt-1">{{ $tenant->subscription->plan->description ?? 'Active subscription plan' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-3xl font-bold">${{ number_format($tenant->subscription->plan->price, 2) }}</div>
                                            <div class="text-indigo-100 text-sm">per month</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-indigo-400">
                                        <div>
                                            <div class="text-indigo-100 text-sm">Start Date</div>
                                            <div class="font-semibold">{{ $tenant->subscription->start_date->format('M d, Y') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-indigo-100 text-sm">End Date</div>
                                            <div class="font-semibold">{{ $tenant->subscription->end_date->format('M d, Y') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-indigo-100 text-sm">Status</div>
                                            <div class="font-semibold">
                                                @if($tenant->subscription->end_date->isPast())
                                                    <span class="px-2 py-1 bg-red-500 rounded text-xs">Expired</span>
                                                @elseif($tenant->subscription->end_date->diffInDays(now()) <= 7)
                                                    <span class="px-2 py-1 bg-yellow-500 rounded text-xs">Expiring Soon</span>
                                                @else
                                                    <span class="px-2 py-1 bg-green-500 rounded text-xs">Active</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-indigo-100 text-sm">Time Remaining</div>
                                            <div class="font-semibold">{{ $tenant->subscription->end_date->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Plan Features</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    @if(is_array($tenant->subscription->plan->features) && count($tenant->subscription->plan->features) > 0)
                                        <ul class="space-y-3">
                                            @foreach($tenant->subscription->plan->features as $key => $value)
                                                <li class="flex items-start">
                                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                        <span class="text-gray-600 ml-1">{{ $value }}</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-500">No features defined</p>
                                    @endif
                                </div>

                                @if($tenant->subscription->end_date->isPast() || $tenant->subscription->end_date->diffInDays(now()) <= 7)
                                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Need to renew?</strong><br>
                                            Contact your administrator to renew your subscription.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Active Subscription</h3>
                            <p class="mt-1 text-sm text-gray-500">Contact your administrator to set up a subscription plan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subscription History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Subscription History</h2>

                    @if($subscriptions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($subscriptions as $subscription)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $subscription->plan->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ number_format($subscription->plan->price, 2) }}/mo
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $subscription->start_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $subscription->end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($subscription->status && $subscription->end_date->isFuture())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @elseif($subscription->status && $subscription->end_date->isPast())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Expired
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('tenant.subscription.show', $subscription->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $subscriptions->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            No subscription history available.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
