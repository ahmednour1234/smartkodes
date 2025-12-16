@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Send Notification (Global)</h2>

                <form method="POST" action="{{ route('admin.notifications.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded border border-gray-200">
                            <p class="text-sm text-gray-700 mb-2 font-medium">Target</p>
                            <p class="text-xs text-gray-500 mb-3">Leave both fields empty to broadcast to all tenant users. Provide tenant_id to broadcast to a single tenant. Provide user_id to notify one user.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tenant ID (optional)</label>
                                    <input type="text" name="tenant_id" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="01ABC..." />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">User ID (optional)</label>
                                    <input type="text" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="01XYZ..." />
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <input type="text" name="type" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="system|info|warning" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" class="mt-1 block w-full border-gray-300 rounded-md" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="message" rows="4" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Send</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
