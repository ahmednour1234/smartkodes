<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Subscription Details</h2>
                        <a href="{{ route('tenant.subscription.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Back to Subscriptions
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="col-span-2">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Information</h3>
                            <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Plan Name:</span>
                                        <p class="text-gray-900 font-medium text-lg">{{ $subscription->plan->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Monthly Price:</span>
                                        <p class="text-gray-900 font-bold text-lg">${{ number_format($subscription->plan->price, 2) }}</p>
                                    </div>
                                </div>

                                @if($subscription->plan->description)
                                    <div>
                                        <span class="text-sm text-gray-500">Description:</span>
                                        <p class="text-gray-700 mt-1">{{ $subscription->plan->description }}</p>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <div>
                                        <span class="text-sm text-gray-500">Start Date:</span>
                                        <p class="text-gray-900 font-medium">{{ $subscription->start_date->format('F d, Y') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">End Date:</span>
                                        <p class="text-gray-900 font-medium">{{ $subscription->end_date->format('F d, Y') }}</p>
                                        @if($subscription->end_date->isPast() && $subscription->status)
                                            <p class="text-sm text-red-600 font-medium mt-1">Expired {{ $subscription->end_date->diffForHumans() }}</p>
                                        @elseif($subscription->end_date->isFuture() && $subscription->status)
                                            <p class="text-sm text-green-600 mt-1">{{ $subscription->end_date->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Status:</span>
                                        <div class="mt-1">
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
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Duration:</span>
                                        <p class="text-gray-900 font-medium">{{ $subscription->start_date->diffInDays($subscription->end_date) }} days</p>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-sm text-gray-500">Created:</span>
                                    <p class="text-gray-900">{{ $subscription->created_at->format('F d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Plan Features</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @if(is_array($subscription->plan->features) && count($subscription->plan->features) > 0)
                                    <ul class="space-y-2">
                                        @foreach($subscription->plan->features as $key => $value)
                                            <li class="flex items-start">
                                                <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span class="text-gray-600 ml-1">{{ $value }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500">No features defined</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($subscription->payments && $subscription->payments->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Payment History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subscription->payments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $payment->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    ${{ number_format($payment->amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $payment->payment_method ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        {{ $payment->status == 'completed' ? 'bg-green-100 text-green-800' :
                                                           ($payment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $payment->transaction_id ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Payment History</h3>
                            <div class="bg-gray-50 p-6 rounded-lg text-center text-gray-500">
                                No payment history available for this subscription.
                            </div>
                        </div>
                    @endif

                    @if($subscription->end_date->isPast() || $subscription->end_date->diffInDays(now()) <= 7)
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-800 mb-1">Subscription Renewal</h4>
                            <p class="text-sm text-yellow-700">
                                Your subscription {{ $subscription->end_date->isPast() ? 'has expired' : 'is expiring soon' }}.
                                Please contact your system administrator to renew your subscription and maintain access to all features.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
