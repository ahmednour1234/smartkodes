@extends('admin.layouts.app')

@section('content')    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Plan</h2>
                        <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Back to Plans
                        </a>
                    </div>

                    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $plan->slug) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $plan->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Price (Monthly)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="price" id="price" value="{{ old('price', $plan->price) }}" step="0.01" min="0" required
                                        class="block w-full pl-7 pr-12 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
                                <div id="features-container" class="space-y-2">
                                    @php
                                        $features = old('features', is_array($plan->features) ? $plan->features : []);
                                    @endphp
                                    @forelse($features as $index => $feature)
                                        <div class="flex gap-2 feature-row">
                                            <input type="text" name="features[{{ $index }}][key]" value="{{ is_array($feature) ? ($feature['key'] ?? '') : '' }}" placeholder="Feature name"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" name="features[{{ $index }}][value]" value="{{ is_array($feature) ? ($feature['value'] ?? '') : $feature }}" placeholder="Value"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <button type="button" onclick="removeFeature(this)" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Remove</button>
                                        </div>
                                    @empty
                                        <div class="flex gap-2 feature-row">
                                            <input type="text" name="features[0][key]" placeholder="Feature name"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" name="features[0][value]" placeholder="Value"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <button type="button" onclick="removeFeature(this)" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Remove</button>
                                        </div>
                                    @endforelse
                                </div>
                                <button type="button" onclick="addFeature()" class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                    Add Feature
                                </button>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ old('status', $plan->status) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $plan->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-4">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Update Plan
                                </button>
                                <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let featureIndex = {{ count($features ?? []) }};

        function addFeature() {
            const container = document.getElementById('features-container');
            const newRow = document.createElement('div');
            newRow.className = 'flex gap-2 feature-row';
            newRow.innerHTML = `
                <input type="text" name="features[${featureIndex}][key]" placeholder="Feature name"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <input type="text" name="features[${featureIndex}][value]" placeholder="Value"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" onclick="removeFeature(this)" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Remove</button>
            `;
            container.appendChild(newRow);
            featureIndex++;
        }

        function removeFeature(button) {
            const rows = document.querySelectorAll('.feature-row');
            if (rows.length > 1) {
                button.closest('.feature-row').remove();
            } else {
                alert('At least one feature is required');
            }
        }
    </script>
@endsection