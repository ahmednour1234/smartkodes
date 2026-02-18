@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Notifications (Global)</h2>
                    <a href="{{ route('admin.notifications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Send Notification</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Read</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($notifications as $n)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $n->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ optional($n->tenant)->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ optional($n->user)->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm"><span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">{{ $n->type }}</span></td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $n->title }}</td>
                                <td class="px-4 py-2 text-sm">{!! $n->read_at ? '<span class="text-green-600">Yes</span>' : '<span class="text-yellow-600">No</span>' !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No notifications yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
