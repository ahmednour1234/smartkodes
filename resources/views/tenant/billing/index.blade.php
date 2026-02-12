@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Billing & Invoices</h2>
                            <p class="text-blue-100 mt-1">Manage your subscription and payment history</p>
                        </div>
                        {{-- TODO: Add subscription management route
                        <a href="{{ route('tenant.billing.subscription') }}"
                           class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                            Manage Subscription
                        </a>
                        --}}
                    </div>
                </div>
            </div>

            <!-- Subscription overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription overview</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Status</p>
                            <p class="font-medium text-gray-900 mt-0.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if(($subscriptionStatus ?? '') === 'Active') bg-green-100 text-green-800
                                    @elseif(($subscriptionStatus ?? '') === 'Pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $subscriptionStatus ?? 'No subscription' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Billing frequency</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $billingFrequency ?? 'Monthly' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Next renewal date</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $nextRenewalDate ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Current plan</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $currentPlan['name'] ?? 'Professional' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Plan & Usage -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Current Plan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Plan</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $currentPlan['name'] ?? 'Professional' }}</div>
                            <div class="text-xl font-semibold text-gray-900 mb-4">${{ $currentPlan['price'] ?? '99' }}/month</div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div>✓ {{ $currentPlan['projects_limit'] ?? 'Unlimited' }} Projects</div>
                                <div>✓ {{ $currentPlan['users_limit'] ?? '10' }} Team Members</div>
                                <div>✓ {{ $currentPlan['forms_limit'] ?? 'Unlimited' }} Forms</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Usage</h3>
                        <p class="text-xs text-gray-500 mb-3">Usage against your plan limits.</p>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Projects</span>
                                    <span>{{ $usage['projects_used'] ?? 0 }}/{{ $usage['projects_limit'] ?? '∞' }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($usage['projects_used'] ?? 0) / max($usage['projects_limit'] ?? 100, 1) * 100, 100) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if(is_numeric($usage['projects_limit'] ?? null))
                                        {{ $usage['projects_used'] ?? 0 }} of {{ $usage['projects_limit'] }} projects used.
                                    @else
                                        Unlimited projects included in your plan.
                                    @endif
                                </p>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Team Members</span>
                                    <span>{{ $usage['users_used'] ?? 0 }}/{{ $usage['users_limit'] ?? 10 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($usage['users_used'] ?? 0) / max($usage['users_limit'] ?? 10, 1) * 100, 100) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $usage['users_used'] ?? 0 }} of {{ $usage['users_limit'] ?? 10 }} team members used.</p>
                            </div>
                            {{-- <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Storage</span>
                                    <span>{{ $usage['storage_used'] ?? '0GB' }}/{{ $usage['storage_limit'] ?? '10GB' }}</span>
                                 </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(($usage['storage_used_mb'] ?? 0) / max($usage['storage_limit_mb'] ?? 10240, 1) * 100, 100) }}%"></div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
                        @if(isset($paymentMethod) && $paymentMethod)
                            <div class="flex items-center mb-4">
                                <div class="h-8 w-12 bg-gray-100 rounded flex items-center justify-center mr-3">
                                    <svg class="h-6 w-6 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium">•••• •••• •••• {{ $paymentMethod['last4'] }}</div>
                                    <div class="text-sm text-gray-600">{{ ucfirst($paymentMethod['brand']) }} • Expires {{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}</div>
                                </div>
                            </div>
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium" disabled title="Coming soon">
                                Update Payment Method
                            </button>
                        @else
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <p class="text-gray-500 mb-4">No payment method on file</p>
                                <button class="bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg" disabled title="Coming soon">
                                    Add Payment Method
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoices -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Invoice History</h3>
                        <div class="flex space-x-2">
                            <input type="text" placeholder="Search invoices..." class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option>All Status</option>
                                <option>Paid</option>
                                <option>Pending</option>
                                <option>Failed</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoices ?? [] as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#{{ $invoice['number'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $invoice['description'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $invoice['date'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format($invoice['amount'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($invoice['status'] == 'paid') bg-green-100 text-green-800
                                                @elseif($invoice['status'] == 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($invoice['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-gray-400 cursor-not-allowed" disabled title="Coming soon">View</button>
                                                @if($invoice['status'] == 'paid')
                                                    <button class="text-gray-400 cursor-not-allowed" disabled title="Coming soon">Download</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No invoices yet</h3>
                                            <p class="text-gray-500">Your invoice history will appear here once you have billing activity.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($invoices) && $invoices->hasPages())
                        <div class="mt-6">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Billing Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Email Notifications</h4>
                            <p class="text-sm text-gray-600 mb-3">Receive email notifications for billing events</p>
                            <label class="flex items-center">
                                <input type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Invoice generated</span>
                            </label>
                            <label class="flex items-center mt-2">
                                <input type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Payment failed</span>
                            </label>
                            <label class="flex items-center mt-2">
                                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Subscription changes</span>
                            </label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Billing Address</h4>
                            <p class="text-sm text-gray-600 mb-3">Update your billing address for invoices</p>
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Update Address
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
