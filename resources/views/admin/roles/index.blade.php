@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Admin Roles</h2>
                    <a href="{{ route('admin.roles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Create Role</a>
                </div>
                <p class="text-gray-600">This is a placeholder; role management UI can be added as needed.</p>
            </div>
        </div>
    </div>
</div>
@endsection
