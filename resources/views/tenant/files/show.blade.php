@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">{{ $file->name }}</h2>
                            <p class="text-blue-100 mt-1">File Details</p>
                        </div>
                        <a href="{{ route('tenant.files.index') }}" class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Files
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- File Preview -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Preview</h3>

                            <div class="border rounded-lg p-4 bg-gray-50">
                                @if(str_contains($file->mime_type, 'image'))
                                    <!-- Image Preview -->
                                    <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="max-w-full h-auto mx-auto rounded">

                                @elseif(str_contains($file->mime_type, 'pdf'))
                                    <!-- PDF Preview -->
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-24 w-24 text-red-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-gray-600 font-semibold mb-2">PDF Document</p>
                                        <a href="{{ Storage::url($file->path) }}" target="_blank" class="text-blue-600 hover:text-blue-700 underline">
                                            Open in new tab
                                        </a>
                                    </div>

                                    <!-- Optional: Embed PDF -->
                                    <iframe src="{{ Storage::url($file->path) }}" class="w-full h-96 mt-4 border rounded"></iframe>

                                @elseif(str_contains($file->mime_type, 'video'))
                                    <!-- Video Preview -->
                                    <video controls class="w-full rounded">
                                        <source src="{{ Storage::url($file->path) }}" type="{{ $file->mime_type }}">
                                        Your browser does not support the video tag.
                                    </video>

                                @else
                                    <!-- Generic File -->
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-gray-600 font-semibold">{{ strtoupper(pathinfo($file->name, PATHINFO_EXTENSION)) }} File</p>
                                        <p class="text-sm text-gray-500 mt-2">No preview available</p>
                                    </div>
                                @endif
                            </div>

                            @if($file->description)
                                <div class="mt-6">
                                    <h4 class="font-semibold text-gray-700 mb-2">Description</h4>
                                    <p class="text-gray-600">{{ $file->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- File Info Sidebar -->
                <div class="space-y-6">
                    <!-- File Details Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">File Information</h3>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-600 font-medium">File Name</p>
                                    <p class="text-gray-900 break-all">{{ $file->name }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-600 font-medium">File Type</p>
                                    <p class="text-gray-900">{{ strtoupper(pathinfo($file->name, PATHINFO_EXTENSION)) }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-600 font-medium">MIME Type</p>
                                    <p class="text-gray-900">{{ $file->mime_type }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-600 font-medium">File Size</p>
                                    <p class="text-gray-900">{{ number_format($file->size / 1024, 2) }} KB</p>
                                </div>

                                @if($file->category)
                                    <div>
                                        <p class="text-gray-600 font-medium">Category</p>
                                        <p class="text-gray-900 capitalize">{{ $file->category }}</p>
                                    </div>
                                @endif

                                <div>
                                    <p class="text-gray-600 font-medium">Uploaded</p>
                                    <p class="text-gray-900">{{ $file->created_at->format('M d, Y g:i A') }}</p>
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

                    <!-- Actions Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Actions</h3>

                            <div class="space-y-3">
                                <a href="{{ Storage::url($file->path) }}" download class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition duration-200">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download
                                </a>

                                <a href="{{ route('tenant.files.edit', $file) }}" class="block w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-center transition duration-200">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Details
                                </a>

                                <form action="{{ route('tenant.files.destroy', $file) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this file? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete File
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
