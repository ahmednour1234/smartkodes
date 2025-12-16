@extends('tenant.layouts.app')

@section('title', 'Create New Record')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Record</h1>
        <p class="text-gray-600 mt-1">Fill out the form to create a new record</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="{{ route('tenant.records.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="work_order_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Work Order <span class="text-red-500">*</span>
                    </label>
                    <select name="work_order_id" id="work_order_id" required class="w-full rounded-md border-gray-300">
                        <option value="">Select Work Order</option>
                        @foreach($workOrders as $wo)
                            <option value="{{ $wo->id }}">{{ $wo->project->name ?? 'Work Order' }} - {{ $wo->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="form_version_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Form Template <span class="text-red-500">*</span>
                    </label>
                    <select name="form_version_id" id="form_version_id" required class="w-full rounded-md border-gray-300">
                        <option value="">Select Form Template</option>
                        @foreach($formVersions as $fv)
                            <option value="{{ $fv->id }}">{{ $fv->form->name ?? 'Form' }} (v{{ $fv->version }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required class="w-full rounded-md border-gray-300">
                        <option value="0">Draft</option>
                        <option value="1">Submitted</option>
                        <option value="2">Approved</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('tenant.records.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Create Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
