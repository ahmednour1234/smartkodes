@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <h2 class="text-2xl font-bold">Account Settings</h2>
                            <p class="text-blue-100 mt-1">Manage your account preferences and organization settings</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Settings Sections -->
            <div class="space-y-6">
                <!-- Organization Profile -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Profile</h3>
                        <form method="POST" action="{{ route('tenant.settings.update-profile') }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="organization_name" class="block text-sm font-medium text-gray-700 mb-2">Organization Name</label>
                                    <input type="text" name="organization_name" id="organization_name"
                                           value="{{ old('organization_name', $tenant->name ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('organization_name') border-red-500 @enderror"
                                           placeholder="Enter organization name">
                                    @error('organization_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="organization_email" class="block text-sm font-medium text-gray-700 mb-2">Organization Email</label>
                                    <input type="email" name="organization_email" id="organization_email"
                                           value="{{ old('organization_email', $tenant->email ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('organization_email') border-red-500 @enderror"
                                           placeholder="Enter organization email">
                                    @error('organization_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" name="phone" id="phone"
                                           value="{{ old('phone', $tenant->phone ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                                           placeholder="Enter phone number">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                    <input type="url" name="website" id="website"
                                           value="{{ old('website', $tenant->website ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('website') border-red-500 @enderror"
                                           placeholder="https://example.com">
                                    @error('website')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" id="address" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                                          placeholder="Enter organization address">{{ old('address', $tenant->address ?? '') }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
                        <form method="POST" action="{{ route('tenant.settings.update-notifications') }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                                        <p class="text-sm text-gray-600">Receive email updates about your account and projects</p>
                                    </div>
                                    <input type="checkbox" name="email_notifications" value="1"
                                           {{ old('email_notifications', $settings['email_notifications'] ?? true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Work Order Assignments</h4>
                                        <p class="text-sm text-gray-600">Get notified when work orders are assigned to you</p>
                                    </div>
                                    <input type="checkbox" name="work_order_notifications" value="1"
                                           {{ old('work_order_notifications', $settings['work_order_notifications'] ?? true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Form Submissions</h4>
                                        <p class="text-sm text-gray-600">Receive notifications for new form submissions</p>
                                    </div>
                                    <input type="checkbox" name="form_submission_notifications" value="1"
                                           {{ old('form_submission_notifications', $settings['form_submission_notifications'] ?? true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Project Updates</h4>
                                        <p class="text-sm text-gray-600">Get notified about project status changes</p>
                                    </div>
                                    <input type="checkbox" name="project_notifications" value="1"
                                           {{ old('project_notifications', $settings['project_notifications'] ?? true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Billing Alerts</h4>
                                        <p class="text-sm text-gray-600">Receive billing and payment notifications</p>
                                    </div>
                                    <input type="checkbox" name="billing_notifications" value="1"
                                           {{ old('billing_notifications', $settings['billing_notifications'] ?? true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                                    Update Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h3>

                        <!-- Change Password -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Change Password</h4>
                            <form method="POST" action="{{ route('tenant.settings.change-password') }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" name="current_password" id="current_password"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                                           required>
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" name="password" id="password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                                               required>
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Two-Factor Authentication -->
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900">Two-Factor Authentication</h4>
                                    <p class="text-sm text-gray-600">Add an extra layer of security to your account</p>
                                </div>
                                <div class="flex items-center">
                                    @if($user->two_factor_enabled ?? false)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-3">Enabled</span>
                                        <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                            Disable 2FA
                                        </button>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 mr-3">Disabled</span>
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                            Enable 2FA
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>

                        <!-- Delete Account -->
                        <div class="border border-red-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900">Delete Organization</h4>
                                    <p class="text-sm text-gray-600">Permanently delete your organization and all associated data. This action cannot be undone.</p>
                                </div>
                                <button onclick="confirmDeleteOrganization()"
                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    Delete Organization
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteOrganization() {
            if (confirm('Are you sure you want to delete your organization? This will permanently delete all projects, forms, work orders, and user data. This action cannot be undone.')) {
                if (confirm('This is your final warning. Type "DELETE" to confirm:')) {
                    const confirmation = prompt('Type "DELETE" to confirm:');
                    if (confirmation === 'DELETE') {
                        // Submit delete form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("tenant.settings.delete-organization") }}';

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        form.appendChild(csrf);

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        form.appendChild(method);

                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            }
        }
    </script>
@endsection
