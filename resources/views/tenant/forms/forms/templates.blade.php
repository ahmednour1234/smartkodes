@extends('tenant.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Form Templates</h2>
                        <a href="{{ route('tenant.forms.index') }}" class="text-gray-600 hover:text-gray-900">← Back to Forms</a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Import Template Section -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Template</h3>
                        <form method="POST" action="{{ route('tenant.forms.import-template') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Form Name</label>
                                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                </div>
                                <div>
                                    <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                                    <select name="project_id" id="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Select a project</option>
                                        @foreach(\App\Models\Project::where('tenant_id', session('tenant_context.current_tenant')->id)->get() as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="template_json" class="block text-sm font-medium text-gray-700">Template JSON</label>
                                <textarea name="template_json" id="template_json" rows="10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm" placeholder='{"name": "Template Name", "schema": {"fields": []}}' required></textarea>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Import Template
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Predefined Templates -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Predefined Templates</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($templates as $template)
                                <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-3">
                                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <h4 class="text-lg font-medium text-gray-900">{{ $template['name'] }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">{{ $template['description'] }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">{{ count($template['schema']['fields']) }} fields</span>
                                        <button onclick="useTemplate({{ json_encode($template) }})" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            Use Template
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- My Templates -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">My Templates</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fields</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $myForms = \App\Models\Form::where('tenant_id', session('tenant_context.current_tenant')->id)
                                            ->where('status', 1) // Only published forms
                                            ->with('formFields')
                                            ->get();
                                    @endphp
                                    @forelse($myForms as $form)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $form->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $form->formFields->count() }} fields
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $form->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="exportTemplate('{{ $form->id }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Export</button>
                                                <a href="{{ route('tenant.forms.clone', $form) }}" class="text-blue-600 hover:text-blue-900">Clone</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                                No published forms available as templates. <a href="{{ route('tenant.forms.create') }}" class="text-blue-600 hover:text-blue-900">Create one now</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function useTemplate(template) {
            // Set the template JSON in the import form
            document.getElementById('template_json').value = JSON.stringify({
                name: template.name,
                schema: template.schema
            }, null, 2);

            // Scroll to the import form
            document.querySelector('.bg-gray-50').scrollIntoView({ behavior: 'smooth' });
        }

        function exportTemplate(formId) {
            // Redirect to export route
            window.location.href = `/admin/forms/${formId}/export`;
        }
    </script>
@endsection
