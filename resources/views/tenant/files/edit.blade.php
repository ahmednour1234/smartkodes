@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Edit File Details</h2>
                            <p class="text-blue-100 mt-1">Update file information</p>
                        </div>
                        <a href="{{ route('tenant.files.show', $file) }}" class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back
                        </a>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Edit Form -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <form action="{{ route('tenant.files.update', $file) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- File Name -->
                                <div class="mb-6">
                                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                                        File Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $file->name) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" required>
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-gray-500 text-xs mt-1">The display name for this file</p>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                                        Description
                                    </label>
                                    <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror" placeholder="Add a description for this file">{{ old('description', $file->description) }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Category -->
                                <!--<div class="mb-6">-->
                                <!--    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">-->
                                <!--        Category-->
                                <!--    </label>-->
                                <!--    <select name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('category') border-red-500 @enderror">-->
                                <!--        <option value="">Select a category</option>-->
                                <!--        <option value="document" {{ old('category', $file->category) == 'document' ? 'selected' : '' }}>Document</option>-->
                                <!--        <option value="image" {{ old('category', $file->category) == 'image' ? 'selected' : '' }}>Image</option>-->
                                <!--        <option value="video" {{ old('category', $file->category) == 'video' ? 'selected' : '' }}>Video</option>-->
                                <!--        <option value="report" {{ old('category', $file->category) == 'report' ? 'selected' : '' }}>Report</option>-->
                                <!--        <option value="form" {{ old('category', $file->category) == 'form' ? 'selected' : '' }}>Form</option>-->
                                <!--        <option value="other" {{ old('category', $file->category) == 'other' ? 'selected' : '' }}>Other</option>-->
                                <!--    </select>-->
                                <!--    @error('category')-->
                                <!--        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>-->
                                <!--    @enderror-->
                                <!--</div>-->

                                <!-- Submit Buttons -->
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('tenant.files.show', $file) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                                        Cancel
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update File
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- File Info Sidebar -->
                <div class="space-y-6">
                    <!-- Current File Preview -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Current File</h3>

                            <div class="border rounded-lg p-4 bg-gray-50">
                                @if(str_contains($file->mime_type, 'image'))
                                    <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="w-full h-auto rounded">
                                @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm font-semibold text-gray-700">{{ strtoupper(pathinfo($file->name, PATHINFO_EXTENSION)) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- File Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">File Details</h3>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-600 font-medium">File Type</p>
                                    <p class="text-gray-900">{{ strtoupper(pathinfo($file->name, PATHINFO_EXTENSION)) }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-600 font-medium">File Size</p>
                                    <p class="text-gray-900">{{ number_format($file->size / 1024, 2) }} KB</p>
                                </div>

                                <div>
                                    <p class="text-gray-600 font-medium">Uploaded</p>
                                    <p class="text-gray-900">{{ $file->created_at->format('M d, Y') }}</p>
                                </div>

                                @if($file->uploaded_by)
                                    <div>
                                        <p class="text-gray-600 font-medium">Uploaded By</p>
                                        <p class="text-gray-900">{{ $file->uploader->name ?? 'Unknown' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm">
                                <p class="font-semibold text-yellow-800 mb-1">Note</p>
                                <p class="text-yellow-700">You can only edit the file name, description, and category. To replace the actual file, please delete this one and upload a new file.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
