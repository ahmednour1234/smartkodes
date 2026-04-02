@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Platform Settings</h2>
                <p class="text-gray-600">Manage support contacts used by dashboard Get Help buttons.</p>

                @if(session('success'))
                    <div class="mt-4 mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">WhatsApp Get Help URL</label>
                            <input type="url" name="whatsapp_help_url" value="{{ old('whatsapp_help_url', $settings['whatsapp_help_url'] ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="https://wa.me/96171824452" />
                            <p class="mt-1 text-xs text-gray-500">This URL is shown as the &ldquo;Get Help&rdquo; button in the header for all users. Format: https://wa.me/&lt;number&gt;</p>
                            @error('whatsapp_help_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
