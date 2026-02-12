@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Notifications</h2>
                            <p class="text-blue-100 mt-1">Stay updated with your team's activities</p>
                        </div>
                        @if(isset($notifications) && count($notifications) > 0)
                        <div class="flex space-x-3">
                            <button onclick="markAllAsRead()"
                                    class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Mark All Read
                            </button>
                            <button onclick="clearAll()"
                                    class="bg-gray-100 text-gray-700 hover:bg-gray-200 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Clear All
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notification Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="filterNotifications('all')" class="filter-btn active px-4 py-2 text-sm font-medium rounded-lg bg-blue-100 text-blue-800">
                            All ({{ $notificationCounts['all'] ?? 0 }})
                        </button>
                        <button onclick="filterNotifications('unread')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200">
                            Unread ({{ $notificationCounts['unread'] ?? 0 }})
                        </button>
                        <button onclick="filterNotifications('work_orders')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200">
                            Work Orders
                        </button>
                        <button onclick="filterNotifications('forms')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200">
                            Forms
                        </button>
                        <button onclick="filterNotifications('projects')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200">
                            Projects
                        </button>
                        <button onclick="filterNotifications('system')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200">
                            System
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scope & preferences -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">What triggers these notifications</h3>
                    <a href="{{ route('tenant.settings.index') }}#notifications" class="text-sm text-blue-600 hover:text-blue-800">Manage preferences</a>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                    <div><span class="font-medium text-gray-800">Projects:</span> Created, updated, or when you are assigned.</div>
                    <div><span class="font-medium text-gray-800">Forms:</span> New submissions, form published, or when you are assigned.</div>
                    <div><span class="font-medium text-gray-800">Work orders:</span> Assigned to you, status changed, or due date updated.</div>
                    <div><span class="font-medium text-gray-800">System:</span> Account, billing, and security-related updates.</div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="space-y-4" id="notifications-container">
                @forelse($notifications ?? [] as $notification)
                    <div class="notification-item bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $notification['read'] ? '' : 'border-l-4 border-blue-500' }}" data-type="{{ $notification['type'] ?? '' }}" data-unread="{{ $notification['read'] ? '0' : '1' }}">
                        <div class="p-6">
                            <div class="flex items-start">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full flex items-center justify-center
                                        @if($notification['type'] == 'work_order') bg-orange-100
                                        @elseif($notification['type'] == 'form') bg-green-100
                                        @elseif($notification['type'] == 'project') bg-blue-100
                                        @elseif($notification['type'] == 'system') bg-purple-100
                                        @else bg-gray-100 @endif">
                                        @if($notification['type'] == 'work_order')
                                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        @elseif($notification['type'] == 'form')
                                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @elseif($notification['type'] == 'project')
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $notification['title'] }}</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-gray-500">{{ $notification['created_at'] }}</span>
                                            @if(!$notification['read'])
                                                <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $notification['message'] }}</p>

                                    <!-- Actions -->
                                    <div class="mt-3 flex items-center space-x-4">
                                        @if(isset($notification['action_url']) && $notification['action_url'])
                                            <a href="{{ $notification['action_url'] }}"
                                               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                {{ $notification['action_text'] ?? 'View Details' }}
                                            </a>
                                        @endif
                                        <button onclick="toggleRead('{{ $notification['id'] }}', {{ $notification['read'] ? 'true' : 'false' }})"
                                                class="text-sm text-gray-600 hover:text-gray-800">
                                            {{ $notification['read'] ? 'Mark as unread' : 'Mark as read' }}
                                        </button>
                                        <button onclick="deleteNotification('{{ $notification['id'] }}')"
                                                class="text-sm text-red-600 hover:text-red-800">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V12h-3v5H7v-5H4v5h5m0 0l5 5m0-5l-5 5" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications yet</h3>
                            <p class="text-gray-500">You'll see notifications here when there are updates to your projects, forms, or work orders.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Load More -->
            @if(isset($notifications) && $notifications->hasPages())
                <div class="text-center mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function filterNotifications(type) {
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                btn.classList.add('bg-gray-100', 'text-gray-800', 'hover:bg-gray-200');
            });

            event.target.classList.add('active', 'bg-blue-100', 'text-blue-800');
            event.target.classList.remove('bg-gray-100', 'text-gray-800', 'hover:bg-gray-200');

            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(notification => {
                const notifType = notification.getAttribute('data-type') || '';
                const isUnread = notification.getAttribute('data-unread') === '1';
                let show = true;
                if (type === 'all') {
                    show = true;
                } else if (type === 'unread') {
                    show = isUnread;
                } else {
                    show = notifType === type;
                }
                notification.style.display = show ? 'block' : 'none';
            });
        }

        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const jsonHeaders = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf()
        });

        function toggleRead(notificationId, isRead) {
            const path = isRead ? 'unread' : 'read';
            fetch(`/tenant/notifications/${notificationId}/${path}`, {
                method: 'POST',
                headers: jsonHeaders()
            })
            .then(r => r.ok ? r.json() : r.text().then(() => ({})))
            .then(data => { if (data.success) location.reload(); });
        }

        function markAllAsRead() {
            if (!confirm('Mark all notifications as read?')) return;
            fetch('/tenant/notifications/mark-all-read', {
                method: 'POST',
                headers: jsonHeaders()
            })
            .then(r => r.ok ? r.json() : r.text().then(() => ({})))
            .then(data => { if (data.success) location.reload(); });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;
            fetch(`/tenant/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            })
            .then(r => r.ok ? r.json() : r.text().then(() => ({})))
            .then(data => { if (data.success) location.reload(); });
        }

        function clearAll() {
            if (!confirm('Clear all notifications? This action cannot be undone.')) return;
            fetch('/tenant/notifications/clear-all', {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
            })
            .then(r => r.ok ? r.json() : r.text().then(() => ({})))
            .then(data => { if (data.success) location.reload(); });
        }
    </script>
@endsection
