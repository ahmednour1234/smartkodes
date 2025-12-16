<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - {{ config('app.name', 'Smart Kodes') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .step-indicator {
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .step-indicator.completed {
            background: #10b981;
            color: white;
        }

        /* Make Select2 look closer to Tailwind inputs */
        .select2-container--default .select2-selection--single {
            height: 48px;
            padding: 6px 12px;
            border: 1px solid #D1D5DB; /* gray-300 */
            border-radius: 0.5rem; /* rounded-lg */
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            font-size: 0.875rem;
            color: #111827; /* gray-900 */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
            right: 10px;
        }

        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8 animate-fadeInUp">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl gradient-bg mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Create Your Account</h1>
                <p class="text-gray-600">Join Smart Kodes and start managing your field operations</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div class="step-indicator active w-10 h-10 rounded-full flex items-center justify-center font-semibold">1</div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Personal Info</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-semibold bg-gray-200">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Company Details</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-semibold bg-gray-200">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Subscription</span>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 animate-fadeInUp">
                <form method="POST" action="{{ route('register') }}" id="registrationForm">
                    @csrf

                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Personal Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="John"
                                />
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="Doe"
                                />
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="john@example.com"
                                />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="+1 (555) 123-4567"
                                />
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Country (from API + Select2) -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                    Country <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="country"
                                    name="country"
                                    required
                                    data-selected="{{ old('country') }}"
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                >
                                    <!-- Options will be loaded via JS -->
                                </select>
                                @error('country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Start typing to search for your country</p>
                            </div>
                        </div>
                    </div>

                    <!-- Company Information Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Company Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Company Name -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    value="{{ old('company_name') }}"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="Acme Corporation"
                                />
                                @error('company_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Field of Work -->
                            <div>
                                <label for="field_of_work" class="block text-sm font-medium text-gray-700 mb-2">
                                    Field of Work <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="field_of_work"
                                    name="field_of_work"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                >
                                    <option value="">Select Field</option>
                                    <option value="Construction" {{ old('field_of_work') == 'Construction' ? 'selected' : '' }}>Construction</option>
                                    <option value="Logistics" {{ old('field_of_work') == 'Logistics' ? 'selected' : '' }}>Logistics</option>
                                    <option value="Healthcare" {{ old('field_of_work') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                    <option value="Retail" {{ old('field_of_work') == 'Retail' ? 'selected' : '' }}>Retail</option>
                                    <option value="Manufacturing" {{ old('field_of_work') == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                    <option value="Field Services" {{ old('field_of_work') == 'Field Services' ? 'selected' : '' }}>Field Services</option>
                                    <option value="Real Estate" {{ old('field_of_work') == 'Real Estate' ? 'selected' : '' }}>Real Estate</option>
                                    <option value="Other" {{ old('field_of_work') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('field_of_work')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Plan Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Choose Your Plan</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Number of Users -->
                            <div>
                                <label for="num_users" class="block text-sm font-medium text-gray-700 mb-2">
                                    Number of Users <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="num_users"
                                    name="num_users"
                                    value="{{ old('num_users', 1) }}"
                                    min="1"
                                    max="100"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    onchange="calculatePrice()"
                                />
                                @error('num_users')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Minimum 1, Maximum 100 users</p>
                            </div>

                            <!-- Price Display -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly Cost
                                </label>
                                <div class="px-4 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-lg">
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-bold text-indigo-600" id="price">$10</span>
                                        <span class="text-gray-600 ml-2">/month</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">$10 per user/month</p>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Features -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold text-gray-900 mb-3">Plan Includes:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Unlimited Projects
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Dynamic Form Builder
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Mobile App Access
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Offline Data Sync
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Advanced Reports
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    24/7 Support
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Account Security</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="••••••••"
                                />
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="••••••••"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Captcha Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-500">Security Verification</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Captcha Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enter the text you see below <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center space-x-3">
                                    <div id="captcha-container" class="border-2 border-gray-300 rounded-lg shadow-sm overflow-hidden">
                                        {!! captcha_img('default') !!}
                                    </div>
                                    <button
                                        type="button"
                                        onclick="refreshCaptcha()"
                                        class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors"
                                        title="Refresh captcha"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Captcha Answer -->
                            <div>
                                <label for="captcha" class="block text-sm font-medium text-gray-700 mb-2">
                                    Captcha Code <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="captcha"
                                    name="captcha"
                                    required
                                    class="input-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                    placeholder="Enter the captcha code"
                                    autocomplete="off"
                                />
                                @error('captcha')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Case insensitive</p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input
                                type="checkbox"
                                name="terms"
                                required
                                class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-600">
                                I agree to the
                                <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Terms of Service</a>
                                and
                                <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Privacy Policy</a>
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                            ← Already have an account?
                        </a>
                        <button
                            type="submit"
                            class="btn-gradient px-8 py-3 text-white font-semibold rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Create Account & Proceed to Payment
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Badge -->
            <div class="mt-6 flex items-center justify-center space-x-6 text-sm text-gray-500">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Secure SSL Encryption</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>GDPR Compliant</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calculatePrice() {
            const numUsers = document.getElementById('num_users').value || 0;
            const pricePerUser = 10;
            const totalPrice = numUsers * pricePerUser;
            document.getElementById('price').textContent = '$' + totalPrice;
        }

        function refreshCaptcha() {
            fetch("/captcha/api/default")
                .then(response => response.json())
                .then(data => {
                    const captchaContainer = document.getElementById('captcha-container');
                    if (captchaContainer && data.img) {
                        captchaContainer.innerHTML = '<img src="' + data.img + '" alt="captcha">';
                    }
                })
                .catch(error => {
                    console.error('Error refreshing captcha:', error);
                    // Fallback: reload the page
                    location.reload();
                });
        }

        // Load countries from REST API & init Select2
        async function initCountrySelect() {
            const $country = $('#country');
            const selectedValue = $country.data('selected') || '';

            // Add a placeholder option
            $country.append(new Option('Loading countries...', '', false, false));

            try {
                const response = await fetch('https://restcountries.com/v3.1/all?fields=cca2,name');
                const data = await response.json();

                // Clear options
                $country.empty();

                // Sort by name
                const countries = data
                    .map(c => ({
                        code: c.cca2,
                        name: c.name.common
                    }))
                    .sort((a, b) => a.name.localeCompare(b.name));

                // Placeholder
                $country.append(new Option('Select Country', '', false, false));

                countries.forEach(country => {
                    const isSelected = selectedValue && selectedValue === country.code;
                    const option = new Option(country.name, country.code, isSelected, isSelected);
                    $country.append(option);
                });
            } catch (error) {
                console.error('Error loading countries:', error);
                // Fallback to a small static list
                $country.empty();
                $country.append(new Option('Select Country', '', false, false));
                const fallback = [
                    { code: 'US', name: 'United States' },
                    { code: 'GB', name: 'United Kingdom' },
                    { code: 'CA', name: 'Canada' },
                    { code: 'AU', name: 'Australia' },
                    { code: 'AE', name: 'United Arab Emirates' },
                    { code: 'LB', name: 'Lebanon' }
                ];
                fallback.forEach(country => {
                    const isSelected = selectedValue && selectedValue === country.code;
                    const option = new Option(country.name, country.code, isSelected, isSelected);
                    $country.append(option);
                });
            }

            // Init Select2
            $country.select2({
                placeholder: 'Select Country',
                allowClear: true,
                width: '100%'
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            calculatePrice();
            initCountrySelect();
        });
    </script>
</body>
</html>
