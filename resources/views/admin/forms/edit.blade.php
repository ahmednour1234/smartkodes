@extends('admin.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Form</h2>
                        <a href="{{ route('admin.forms.index') }}" class="text-gray-600 hover:text-gray-900">← Back to Forms</a>
                    </div>

                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Form Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $form->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $form->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="0" {{ (old('status') ?? $form->status) == '0' ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ (old('status') ?? $form->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="2" {{ (old('status') ?? $form->status) == '2' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Only Active forms can be assigned to work orders</p>
                        </div>

                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">To Edit Form Fields</h3>
                                    <p class="mt-1 text-sm text-blue-700">
                                        Use the <a href="{{ route('admin.forms.builder', $form) }}" class="font-semibold underline hover:text-blue-900">Form Builder</a> to add, edit, or rearrange fields with drag & drop.
                                        This page is only for updating the form name, description, and status.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
