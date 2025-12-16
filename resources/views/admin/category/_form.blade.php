@php
    /** @var \App\Models\Category|null $category */
    $category = $category ?? null;
@endphp

<div class="space-y-6">
    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Name <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $category?->name) }}"
            class="block w-full rounded-lg border-gray-300 shadow-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   @error('name') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
            placeholder="e.g., Safety, Maintenance, Customer Feedback"
            required
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-500">
            Choose a short, clear name that describes this category.
        </p>
    </div>

    {{-- Description --}}
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description <span class="text-gray-400 text-xs">(optional)</span>
        </label>
        <textarea
            name="description"
            id="description"
            rows="4"
            class="block w-full rounded-lg border-gray-300 shadow-sm px-3 py-2
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   @error('description') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
            placeholder="Describe what type of forms belong to this category..."
        >{{ old('description', $category?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror>
        <p class="mt-1 text-xs text-gray-500">
            This helps your team understand when to use this category.
        </p>
    </div>
</div>
