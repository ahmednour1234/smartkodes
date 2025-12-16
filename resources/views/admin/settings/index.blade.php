@extends('admin.layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Platform Settings</h2>
                <p class="text-gray-600">Settings UI placeholder. Managed via existing Admin\SettingController.</p>
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Support Email</label>
                            <input type="email" name="support_email" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="support@example.com" />
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
