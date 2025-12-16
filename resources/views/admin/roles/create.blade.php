@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Create Role (Admin)</h2>

                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 block w-full border-gray-300 rounded-md" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Permissions</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach(($permissions ?? []) as $perm)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300" name="permission_ids[]" value="{{ $perm->id }}">
                                    <span class="ml-2 text-sm">{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Create</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
