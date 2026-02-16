<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Notification;
use App\Models\FormField;
use App\Models\FormVersion;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormsExport;

class FormController extends Controller
{
    /**
     * Get the view prefix based on current route.
     */
    private function getViewPrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on current route.
     */
    private function getRoutePrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $categoryId = $request->get('category_id');

        $query = Form::where('tenant_id', $currentTenant->id)
            ->with(['creator', 'workOrders', 'category']);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $forms = $query->paginate(15)->appends($request->query());

        $categories = Category::orderBy('name')->get();

        $viewPrefix = $this->getViewPrefix();

        return view("{$viewPrefix}.forms.index", compact('forms', 'categories', 'categoryId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $categories = Category::orderBy('name')->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.forms.create", compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'name'        => 'required|string|max:255|unique:forms,name,NULL,id,tenant_id,' . $currentTenant->id . ',deleted_at,NULL',
            'description' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $form = Form::create([
            'tenant_id'   => $currentTenant->id,
            'project_id'  => $request->project_id, // لو موجود في الفورم
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'schema_json' => json_encode(['fields' => []]), // Empty schema, will be built in builder
            'version'     => 1,
            'status'      => 0, // Draft
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.builder", $form)
            ->with('success', 'Form template created! Now drag and drop fields to build your form.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)
            ->with(['creator', 'formFields', 'workOrders.project', 'category'])
            ->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.forms.show", compact('form'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $categories = Category::orderBy('name')->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.forms.edit", compact('form', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255|unique:forms,name,' . $id . ',id,tenant_id,' . $currentTenant->id . ',deleted_at,NULL',
            'description' => 'nullable|string|max:500',
            'status'      => 'required|integer|in:0,1,2',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $form->update([
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
            'category_id' => $request->category_id,
            'updated_by'  => Auth::id(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.index")
            ->with('success', 'Form template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // هنا لو عايز تمنع الحذف لو فيه Records أو WorkOrders تقدر تضيف checks

        $form->delete(); // Soft delete لو الموديل فيه SoftDeletes

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.index")
            ->with('success', 'Form deleted successfully.');
    }

    /**
     * Show the form builder interface.
     */
    public function builder(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.forms.builder", compact('form'));
    }

    /**
     * Save form builder data.
     */
    public function saveBuilder(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);

        $request->validate([
            'schema' => 'required|array',
            'fields' => 'required|array',
        ]);

        // Save form schema
        $form->update([
            'schema_json' => $request->schema,
            'updated_by'  => Auth::id(),
        ]);

        // Save form fields
        $form->formFields()->delete(); // Remove existing fields

        foreach ($request->fields as $index => $fieldData) {
            FormField::create([
                'tenant_id'   => $currentTenant->id,
                'form_id'     => $form->id,
                'name'        => $fieldData['key'],
                'type'        => $fieldData['type'],
                'config_json' => $fieldData,
                'order'       => $index,
            ]);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.builder", $form->id)
            ->with('success', 'Form saved successfully!');
    }

    /**
     * Publish a form (change status from draft to live).
     */
    public function publish(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // Create a new version
        FormVersion::create([
            'tenant_id'   => $currentTenant->id,
            'form_id'     => $form->id,
            'version'     => $form->version,
            'schema_json' => $form->schema_json,
            'created_by'  => Auth::id(),
        ]);

        // Update form status and increment version
        $form->update([
            'status'     => 1, // Live
            'version'    => $form->version + 1,
            'updated_by' => Auth::id(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        $formUrl = route("{$routePrefix}.forms.show", $form->id);
        $notifiedIds = [Auth::id()];
        if ($form->created_by && !in_array($form->created_by, $notifiedIds)) {
            Notification::create([
                'tenant_id' => $currentTenant->id,
                'user_id' => $form->created_by,
                'type' => 'form',
                'title' => 'Form published',
                'message' => 'Form "' . $form->name . '" has been published',
                'data' => ['form_id' => $form->id],
                'action_url' => $formUrl,
                'created_by' => Auth::id(),
            ]);
            $notifiedIds[] = $form->created_by;
        }
        $form->workOrders()->with('assignedUser')->get()->pluck('assignedUser')->filter()->unique('id')->each(function ($user) use ($currentTenant, $form, $formUrl, &$notifiedIds) {
            if ($user && !in_array($user->id, $notifiedIds)) {
                Notification::create([
                    'tenant_id' => $currentTenant->id,
                    'user_id' => $user->id,
                    'type' => 'form',
                    'title' => 'Form published',
                    'message' => 'Form "' . $form->name . '" has been published and is available for your work orders',
                    'data' => ['form_id' => $form->id],
                    'action_url' => $formUrl,
                    'created_by' => Auth::id(),
                ]);
                $notifiedIds[] = $user->id;
            }
        });

        return redirect()->route("{$routePrefix}.forms.index")->with('success', 'Form published successfully');
    }

    /**
     * Clone a form (copy with all main attributes + fields).
     */
    public function clone(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $originalForm = Form::where('tenant_id', $currentTenant->id)
            ->with('formFields')
            ->findOrFail($id);

        $clonedForm = Form::create([
            'tenant_id'   => $currentTenant->id,
            'project_id'  => $originalForm->project_id,
            'name'        => $originalForm->name . ' (Copy)',
            'category_id' => $originalForm->category_id,
            'description' => $originalForm->description,
            'schema_json' => $originalForm->schema_json,
            'version'     => 1,
            'status'      => 0, // Draft
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        // Clone form fields
        foreach ($originalForm->formFields as $field) {
            FormField::create([
                'tenant_id'   => $currentTenant->id,
                'form_id'     => $clonedForm->id,
                'name'        => $field->name,
                'type'        => $field->type,
                'config_json' => $field->config_json,
                'order'       => $field->order,
            ]);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.edit", $clonedForm)
            ->with('success', 'Form cloned successfully');
    }

    /**
     * Show form templates.
     */
    public function templates()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        // For now, return some predefined templates
        $templates = [
            [
                'name'        => 'Contact Form',
                'description' => 'Basic contact information form',
                'schema'      => [
                    'fields' => [
                        [
                            'type'     => 'text',
                            'key'      => 'first_name',
                            'label'    => 'First Name',
                            'required' => true,
                        ],
                        [
                            'type'     => 'text',
                            'key'      => 'last_name',
                            'label'    => 'Last Name',
                            'required' => true,
                        ],
                        [
                            'type'     => 'text',
                            'key'      => 'email',
                            'label'    => 'Email',
                            'required' => true,
                        ],
                        [
                            'type'  => 'text',
                            'key'   => 'phone',
                            'label' => 'Phone',
                        ],
                    ],
                ],
            ],
            [
                'name'        => 'Survey Form',
                'description' => 'Customer satisfaction survey',
                'schema'      => [
                    'fields' => [
                        [
                            'type'     => 'radio',
                            'key'      => 'satisfaction',
                            'label'    => 'Overall Satisfaction',
                            'options'  => ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied'],
                            'required' => true,
                        ],
                        [
                            'type'  => 'textarea',
                            'key'   => 'comments',
                            'label' => 'Comments',
                        ],
                    ],
                ],
            ],
        ];

        $viewPrefix = $this->getViewPrefix();
        // خلى الباث أبسط شوية
        return view("{$viewPrefix}.forms.forms.templates", compact('templates'));
    }

    /**
     * Import form template.
     */
    public function importTemplate(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'template_json' => 'required|json',
            'project_id'    => 'required|exists:projects,id',
            'name'          => 'required|string|max:255',
        ]);

        $templateData = json_decode($request->template_json, true);

        $form = Form::create([
            'tenant_id'   => $currentTenant->id,
            'project_id'  => $request->project_id,
            'name'        => $request->name,
            'schema_json' => $templateData['schema'] ?? $templateData,
            'version'     => 1,
            'status'      => 0, // Draft
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        // Create form fields if they exist in template
        if (isset($templateData['fields'])) {
            foreach ($templateData['fields'] as $index => $fieldData) {
                FormField::create([
                    'tenant_id'   => $currentTenant->id,
                    'form_id'     => $form->id,
                    'name'        => $fieldData['key'] ?? $fieldData['name'],
                    'type'        => $fieldData['type'],
                    'config_json' => $fieldData,
                    'order'       => $index,
                ]);
            }
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.forms.builder", $form)
            ->with('success', 'Template imported successfully');
    }

    /**
     * Export forms list to Excel/CSV.
     */
    public function exportList(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $filters = [
            'status'      => $request->input('status'),
            'category_id' => $request->input('category_id'),
        ];

        $format   = $request->input('format', 'xlsx'); // 'xlsx' or 'csv'
        $filename = 'forms_' . date('Y-m-d_His') . '.' . $format;

        return Excel::download(
            new FormsExport($currentTenant->id, $filters),
            $filename,
            $format === 'csv'
                ? \Maatwebsite\Excel\Excel::CSV
                : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export form as JSON.
     */
    public function export(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)
            ->with('formFields')
            ->findOrFail($id);

        $exportData = [
            'name'        => $form->name,
            'schema'      => $form->schema_json,
            'fields'      => $form->formFields->map(function ($field) {
                return $field->config_json;
            })->toArray(),
            'exported_at' => now()->toISOString(),
            'version'     => $form->version,
        ];

        return response()->json($exportData, 200, [
            'Content-Disposition' => 'attachment; filename="' . $form->name . '.json"',
        ]);
    }

    /**
     * Render form for public/end-user viewing and submission.
     */
    public function render(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)
            ->where('status', 1) // Only show live forms
            ->with(['formFields' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.forms.render", compact('form'));
    }

    /**
     * Submit form data and create record.
     */
    public function submit(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $form = Form::where('tenant_id', $currentTenant->id)
            ->where('status', 1)
            ->with('formFields')
            ->findOrFail($id);

        // Validate form submission
        $this->validateFormSubmission($request, $form);

        // Create record
        $record = \App\Models\Record::create([
            'tenant_id'     => $currentTenant->id,
            'project_id'    => $form->project_id,
            'form_id'       => $form->id,
            'work_order_id' => $request->input('work_order_id'), // Optional
            'submitted_by'  => Auth::id(),
            'submitted_at'  => now(),
            'location'      => $request->input('_location') ? json_encode($request->input('_location')) : null,
            'ip_address'    => $request->ip(),
            'status'        => 1, // Submitted
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
        ]);

        // Store field values
        foreach ($form->formFields as $formField) {
            $fieldValue = $request->input($formField->name);

            // Handle file uploads
            if (in_array($formField->type, ['file', 'photo', 'video', 'audio'], true) && $request->hasFile($formField->name)) {
                $fieldValue = $this->handleFileUpload($request->file($formField->name), $formField, $record);
            }

            // Handle calculated fields
            if ($formField->type === 'calculated' && $formField->calculation_formula) {
                $fieldValue = $this->evaluateFormula($formField->calculation_formula, $request->all());
            }

            // Required فلاغ بسيط بدل تعبير معقد
            $config = is_array($formField->config_json) ? $formField->config_json : (array) $formField->config_json;
            $isRequired = $config['required'] ?? false;

            // Skip empty values for non-required fields
            if ($fieldValue === null && !$isRequired) {
                continue;
            }

            \App\Models\RecordField::create([
                'tenant_id'     => $currentTenant->id,
                'record_id'     => $record->id,
                'form_field_id' => $formField->id,
                'value_json'    => is_array($fieldValue) ? $fieldValue : ['value' => $fieldValue],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Form submitted successfully',
                'record_id' => $record->id,
            ]);
        }

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.records.show", $record)
            ->with('success', 'Form submitted successfully!');
    }

    /**
     * Validate form submission against form field rules.
     */
    private function validateFormSubmission(Request $request, Form $form)
    {
        $rules    = [];
        $messages = [];

        foreach ($form->formFields as $field) {
            $fieldRules = [];

            // config_json safe cast
            $config = is_array($field->config_json) ? $field->config_json : (array) $field->config_json;
            $isRequired = $config['required'] ?? false;

            // Required validation
            if ($isRequired) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                case 'currency':
                    $fieldRules[] = 'numeric';
                    if ($field->min_value !== null) {
                        $fieldRules[] = 'min:' . $field->min_value;
                    }
                    if ($field->max_value !== null) {
                        $fieldRules[] = 'max:' . $field->max_value;
                    }
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'time':
                    $fieldRules[] = 'date_format:H:i';
                    break;
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                case 'photo':
                case 'video':
                case 'audio':
                    $fieldRules[] = 'file';
                    if ($field->type === 'photo') {
                        $fieldRules[] = 'image';
                        $fieldRules[] = 'max:5120'; // 5MB
                    } elseif ($field->type === 'video') {
                        $fieldRules[] = 'mimes:mp4,avi,mov';
                        $fieldRules[] = 'max:51200'; // 50MB
                    } elseif ($field->type === 'audio') {
                        $fieldRules[] = 'mimes:mp3,wav,ogg';
                        $fieldRules[] = 'max:10240'; // 10MB
                    } else {
                        $fieldRules[] = 'max:10240'; // 10MB
                    }
                    break;
                case 'select':
                case 'radio':
                    if (!empty($field->options)) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
                case 'multiselect':
                    $fieldRules[] = 'array';
                    if (!empty($field->options)) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
            }

            // Regex validation
            if (!empty($field->regex_pattern)) {
                $fieldRules[] = 'regex:' . $field->regex_pattern;

                $label = $config['label'] ?? $field->name;
                $messages[$field->name . '.regex'] = 'The ' . $label . ' format is invalid.';
            }

            if (!empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }

        $request->validate($rules, $messages);
    }

    /**
     * Handle file upload and return file path.
     */
    private function handleFileUpload($file, FormField $formField, $record)
    {
        $currentTenant = session('tenant_context.current_tenant');

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'tenants/' . $currentTenant->id . '/records/' . $record->id,
            $filename,
            'public'
        );

        // Create file record
        \App\Models\File::create([
            'tenant_id'    => $currentTenant->id,
            'fileable_id'  => $record->id,
            'fileable_type'=> \App\Models\Record::class,
            'name'         => $file->getClientOriginalName(),
            'path'         => $path,
            'mime_type'    => $file->getMimeType(),
            'size'         => $file->getSize(),
            'uploaded_by'  => Auth::id(),
        ]);

        return $path;
    }

    /**
     * Evaluate calculated field formula safely using Symfony ExpressionLanguage.
     */
    private function evaluateFormula(string $formula, array $fieldValues)
    {
        try {
            $expressionLanguage = new ExpressionLanguage();

            // Register common math functions
            $expressionLanguage->register('min', function (...$args) {
                return sprintf('min(%s)', implode(', ', $args));
            }, function ($arguments, ...$values) {
                return min(...$values);
            });

            $expressionLanguage->register('max', function (...$args) {
                return sprintf('max(%s)', implode(', ', $args));
            }, function ($arguments, ...$values) {
                return max(...$values);
            });

            $expressionLanguage->register('round', function ($value, $precision = 0) {
                return sprintf('round(%s, %s)', $value, $precision);
            }, function ($arguments, $value, $precision = 0) {
                return round($value, $precision);
            });

            $expressionLanguage->register('abs', function ($value) {
                return sprintf('abs(%s)', $value);
            }, function ($arguments, $value) {
                return abs($value);
            });

            $expressionLanguage->register('sqrt', function ($value) {
                return sprintf('sqrt(%s)', $value);
            }, function ($arguments, $value) {
                return sqrt($value);
            });

            $expressionLanguage->register('pow', function ($base, $exp) {
                return sprintf('pow(%s, %s)', $base, $exp);
            }, function ($arguments, $base, $exp) {
                return pow($base, $exp);
            });

            // Replace field references {field_name} with expression language variables
            $expression = $formula;
            preg_match_all('/\{([^}]+)\}/', $formula, $matches);

            $variables = [];
            if (!empty($matches[1])) {
                foreach ($matches[1] as $fieldName) {
                    $value = $fieldValues[$fieldName] ?? 0;
                    $value = is_numeric($value) ? (float) $value : 0;
                    $variables[$fieldName] = $value;

                    $expression = str_replace('{' . $fieldName . '}', $fieldName, $expression);
                }
            }

            $result = $expressionLanguage->evaluate($expression, $variables);

            return is_numeric($result) ? round($result, 2) : 0;
        } catch (\Throwable $e) {
            Log::error('Calculated field evaluation error', [
                'formula'      => $formula,
                'error'        => $e->getMessage(),
                'field_values' => $fieldValues,
            ]);

            return 0;
        }
    }
}
