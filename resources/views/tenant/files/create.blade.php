@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Upload File</h2>
                            <p class="text-blue-100 mt-1">Upload a new file or document</p>
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

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('tenant.files.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        {{-- Hidden record_id = آخر Record للـ tenant --}}
                        @if(!empty($lastRecord))
                            <input type="hidden" name="record_id" value="{{ $lastRecord->id }}">
                        @endif

                        <!-- File Upload Area -->
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                File <span class="text-red-500">*</span>
                            </label>

                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition duration-200" id="dropZone">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>

                                <p class="text-gray-600 mb-2">
                                    <label for="file" class="text-blue-600 hover:text-blue-700 cursor-pointer font-semibold">
                                        Click to upload
                                    </label>
                                    or drag and drop
                                </p>
                                <p class="text-sm text-gray-500">
                                    PDF, Word, Excel, Images, Videos (Max 50MB)
                                </p>

                                <input type="file" name="file" id="file" class="hidden" required>

                                <div id="fileInfo" class="hidden mt-4">
                                    <div class="bg-blue-50 rounded-lg p-4 inline-block">
                                        <p class="text-sm font-semibold text-blue-900" id="fileName"></p>
                                        <p class="text-xs text-blue-600" id="fileSize"></p>
                                    </div>
                                </div>
                            </div>
                            @error('file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                                File Name (Optional)
                            </label>
                            <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Leave blank to use original filename">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                                Description (Optional)
                            </label>
                            <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Add a description for this file"></textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <!--<div class="mb-6">-->
                        <!--    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">-->
                        <!--        Category (Optional)-->
                        <!--    </label>-->
                        <!--    <select name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">-->
                        <!--        <option value="">Select a category</option>-->
                        <!--        <option value="document">Document</option>-->
                        <!--        <option value="image">Image</option>-->
                        <!--        <option value="video">Video</option>-->
                        <!--        <option value="report">Report</option>-->
                        <!--        <option value="form">Form</option>-->
                        <!--        <option value="other">Other</option>-->
                        <!--    </select>-->
                        <!--    @error('category')-->
                        <!--        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>-->
                        <!--    @enderror-->
                        <!--</div>-->

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="hidden mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-blue-900">Uploading...</span>
                                    <span class="text-sm text-blue-600" id="progressPercent">0%</span>
                                </div>
                                <div class="w-full bg-blue-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%" id="progressBar"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('tenant.files.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('file');
        const dropZone = document.getElementById('dropZone');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const nameInput = document.getElementById('name');

        // File input change
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                displayFileInfo(this.files[0]);
            }
        });

        // Drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');

            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                displayFileInfo(e.dataTransfer.files[0]);
            }
        });

        function displayFileInfo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('hidden');

            // Auto-fill name if empty
            if (!nameInput.value) {
                nameInput.value = file.name;
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form submission (simple state)
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Uploading...';
        });
    </script>
@endsection
