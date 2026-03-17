@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Edit Admin User</h2>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full border-gray-300 rounded-md" required />
                            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full border-gray-300 rounded-md" required />
                            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Photo</label>
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="mt-2 h-16 w-16 rounded-full object-cover border border-gray-200">
                            @endif
                            <input type="file" name="photo" accept="image/*" class="mt-2 block w-full border-gray-300 rounded-md" />
                            <p class="mt-1 text-xs text-gray-500">Optional. Uploading a new photo replaces the current one.</p>
                            @error('photo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">User ID Copy</label>
                            @if($user->id_copy_url)
                                <a href="{{ $user->id_copy_url }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">View current file</a>
                            @endif
                            <input type="file" name="id_copy" accept=".jpg,.jpeg,.png,.pdf" class="mt-2 block w-full border-gray-300 rounded-md" />
                            <p class="mt-1 text-xs text-gray-500">Optional. Uploading a new file replaces the current one.</p>
                            @error('id_copy')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Driving License Copy</label>
                            @if($user->driving_license_url)
                                <a href="{{ $user->driving_license_url }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">View current file</a>
                            @endif
                            <input type="file" name="driving_license" accept=".jpg,.jpeg,.png,.pdf" class="mt-2 block w-full border-gray-300 rounded-md" />
                            <p class="mt-1 text-xs text-gray-500">Optional. Uploading a new file replaces the current one.</p>
                            @error('driving_license')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                            <input type="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md" />
                            @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Roles</label>
                            <select name="role_ids[]" multiple class="mt-1 block w-full border-gray-300 rounded-md">
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $user->roles->pluck('id')->contains($role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_ids')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Save Changes</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
