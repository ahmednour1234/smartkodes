@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $role->name }}</h2>
                    <div class="space-x-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Edit</a>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Delete this role?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Slug</p>
                        <p class="text-gray-900">{{ $role->slug }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-gray-900">{{ $role->status ? 'Active' : 'Inactive' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="text-gray-900">{{ $role->description ?: '—' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Permissions</p>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @forelse($role->permissions as $perm)
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">{{ $perm->name }}</span>
                            @empty
                                <span class="text-gray-500">No permissions assigned.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
