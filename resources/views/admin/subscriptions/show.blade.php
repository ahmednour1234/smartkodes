<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Subscription Details</h2>
                        <div class="flex gap-2">
                            @if($subscription->status)
                                <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Cancel Subscription
                                    </button>
                                </form>
                                <button onclick="document.getElementById('renewModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Renew Subscription
                                </button>
                            @endif
                            <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                Edit
                            </a>
                            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Back
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="col-span-2">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Information</h3>
                            <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Tenant:</span>
                                        <p class="text-gray-900 font-medium">
                                            <a href="{{ route('admin.tenants.show', $subscription->tenant) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $subscription->tenant->name }}
                                            </a>
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $subscription->tenant->domain }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Plan:</span>
                                        <p class="text-gray-900 font-medium">
                                            <a href="{{ route('admin.plans.show', $subscription->plan) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $subscription->plan->name }}
                                            </a>
                                        </p>
                                        <p class="text-sm text-gray-500">${{ number_format($subscription->plan->price, 2) }}/month</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Start Date:</span>
                                        <p class="text-gray-900">{{ $subscription->start_date->format('F d, Y') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">End Date:</span>
                                        <p class="text-gray-900">{{ $subscription->end_date->format('F d, Y') }}</p>
                                        @if($subscription->end_date->isPast() && $subscription->status)
                                            <p class="text-sm text-red-600 font-medium">Expired {{ $subscription->end_date->diffForHumans() }}</p>
                                        @elseif($subscription->end_date->isFuture() && $subscription->status)
                                            <p class="text-sm text-green-600">{{ $subscription->end_date->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Status:</span>
                                        <div class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $subscription->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Duration:</span>
                                        <p class="text-gray-900">{{ $subscription->start_date->diffInDays($subscription->end_date) }} days</p>
                                    </div>
                                </div>

                                @if($subscription->creator)
                                    <div>
                                        <span class="text-sm text-gray-500">Created By:</span>
                                        <p class="text-gray-900">{{ $subscription->creator->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $subscription->created_at->format('F d, Y h:i A') }}</p>
                                    </div>
                                @endif
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
                                                <div>
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ is_numeric($key) ? ($value['key'] ?? 'Feature') : $key }}:
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        {{ is_array($value) ? ($value['value'] ?? '') : $value }}
                                                    </span>
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
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Modal -->
    <div id="renewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Renew Subscription</h3>
                <form action="{{ route('admin.subscriptions.renew', $subscription) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="duration_months" class="block text-sm font-medium text-gray-700 mb-2">
                            Extend subscription by (months):
                        </label>
                        <input type="number" name="duration_months" id="duration_months" min="1" max="24" value="1" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('renewModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Renew
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
