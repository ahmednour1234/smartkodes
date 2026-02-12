@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">Files</h2>
                            <p class="text-blue-100 mt-1">Manage your uploaded files and documents</p>
                            <p class="text-blue-200 text-sm mt-1">Supported: PDF, JPG, PNG, and others — max 50MB per file</p>
                        </div>
                        <a href="{{ route('tenant.files.create') }}" class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload File
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Files List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($files->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($files as $file)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition duration-200">
                                    <!-- File Icon -->
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-blue-100 rounded flex items-center justify-center mr-3">
                                            @if(str_contains($file->mime_type, 'image'))
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif(str_contains($file->mime_type, 'pdf'))
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif(str_contains($file->mime_type, 'video'))
                                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $file->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ number_format($file->size / 1024, 2) }} KB</p>
                                        </div>
                                    </div>

                                    <!-- File Details -->
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Type:</span>
                                            <span class="text-gray-900 font-medium">{{ strtoupper(pathinfo($file->name, PATHINFO_EXTENSION)) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Uploaded:</span>
                                            <span class="text-gray-900">{{ $file->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if($file->uploaded_by)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">By:</span>
                                                <span class="text-gray-900">{{ $file->uploader->name ?? 'Unknown' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-4 flex space-x-2">
                                        <a href="{{ route('tenant.files.show', $file) }}" class="flex-1 bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded text-sm font-medium text-center transition duration-200">
                                            View
                                        </a>
                                        <a href="{{ Storage::url($file->path) }}" download class="flex-1 bg-green-50 text-green-700 hover:bg-green-100 px-3 py-2 rounded text-sm font-medium text-center transition duration-200">
                                            Download
                                        </a>
                                        <form action="{{ route('tenant.files.destroy', $file) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this file?')" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full bg-red-50 text-red-700 hover:bg-red-100 px-3 py-2 rounded text-sm font-medium transition duration-200">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $files->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-4 text-gray-600">No files uploaded yet</p>
                            <p class="text-sm text-gray-500 mt-2">Upload your first file to get started</p>
                            <a href="{{ route('tenant.files.create') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Upload File
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
