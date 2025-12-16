<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Checkout - {{ config('app.name', 'Smart Kodes') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8 animate-fadeInUp">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl gradient-bg mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Complete Your Payment</h1>
                <p class="text-gray-600">You're almost there! Complete payment to activate your account.</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Summary -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-xl p-8 animate-fadeInUp">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Order Summary</h2>

                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900">Company</p>
                                    <p class="text-sm text-gray-500">{{ $tenant->company_name ?? $tenant->name }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900">Number of Users</p>
                                    <p class="text-sm text-gray-500">{{ $numUsers }} user(s)</p>
                                </div>
                                <p class="font-semibold text-gray-900">${{ number_format($numUsers * 10, 2) }}</p>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900">Billing Period</p>
                                    <p class="text-sm text-gray-500">Monthly subscription</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center py-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg px-4">
                                <p class="text-lg font-bold text-gray-900">Total Amount</p>
                                <p class="text-2xl font-bold text-indigo-600">${{ number_format($amount, 2) }}</p>
                            </div>
                        </div>

                        <!-- Plan Features Reminder -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Your Plan Includes:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Unlimited Projects
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Dynamic Form Builder
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Mobile App Access
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Offline Data Sync
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Advanced Reports
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    24/7 Support
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form method="POST" action="{{ route('payment.process') }}">
                            @csrf

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Method
                                </label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-4 border-2 border-indigo-500 rounded-lg cursor-pointer bg-indigo-50">
                                        <input type="radio" name="payment_method" value="bank_transfer" checked class="mr-3">
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <div>
                                                <p class="font-semibold text-gray-900">Bank Transfer</p>
                                                <p class="text-sm text-gray-600">Direct bank transfer via payment gateway</p>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-300">
                                        <input type="radio" name="payment_method" value="credit_card" class="mr-3">
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <div>
                                                <p class="font-semibold text-gray-900">Credit Card</p>
                                                <p class="text-sm text-gray-600">Pay with Visa, Mastercard, or Amex</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn-gradient w-full py-4 text-white font-semibold rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Proceed to Payment Gateway
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Security Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-xl p-6 animate-fadeInUp sticky top-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Secure Payment</h3>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">SSL Encrypted</p>
                                    <p class="text-sm text-gray-600">Your payment information is secure</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">PCI Compliant</p>
                                    <p class="text-sm text-gray-600">Industry-standard security</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Money-back Guarantee</p>
                                    <p class="text-sm text-gray-600">30-day refund policy</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500 leading-relaxed">
                                By completing this payment, you agree to our Terms of Service and acknowledge our Privacy Policy. You will be charged monthly until you cancel your subscription.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Login Link -->
            <div class="text-center mt-8">
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
