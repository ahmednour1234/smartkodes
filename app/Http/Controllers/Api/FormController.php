<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Form\UpdateFormDataRequest;
use App\Http\Resources\Api\FormResource;
use App\Http\Resources\Api\RecordResource;
use App\Repositories\FormRepository;
use App\Services\ApiResponseService;
use App\Services\FormService;
use App\Models\Record;
use App\Models\RecordField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends BaseApiController
{
    protected FormRepository $repository;
    protected FormService $formService;

    public function __construct(ApiResponseService $response, FormRepository $repository, FormService $formService)
    {
        parent::__construct($response);
        $this->repository = $repository;
        $this->formService = $formService;
    }

    /**
     * Get list of forms (tenant-scoped)
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'category_id', 'search']);
            $perPage = $request->get('per_page', 15);

            $forms = $this->repository->getWithFilters($filters, $perPage);

            return $this->paginatedResponse(
                FormResource::collection($forms),
                'Forms retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve forms: ' . $e->getMessage());
        }
    }

    /**
     * Get single form with all fields
     */
    public function show(string $id)
    {
        try {
            $form = $this->repository->getForSubmission($id);

            return $this->successResponse(
                new FormResource($form),
                'Form retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->notFoundResponse('Form not found');
        }
    }

    /**
     * Update form data for a record (add missing data)
     */
    public function updateFormData(UpdateFormDataRequest $request, string $formId, string $recordId)
    {
        try {
            $user = Auth::user();
            
            // Find record
            $record = Record::where('tenant_id', $user->tenant_id)
                ->where('id', $recordId)
                ->where('form_id', $formId)
                ->firstOrFail();

            // Verify user can update this record
            if ($record->submitted_by !== $user->id && !$user->hasAnyRole(['admin', 'manager'])) {
                return $this->forbiddenResponse('You are not authorized to update this record');
            }

            // Load form with fields
            $form = $this->repository->getForSubmission($formId);

            $validated = $request->validated();

            $maxFileBytes = 5 * 1024 * 1024; // 5MB

            // Update or create record fields
            foreach ($form->formFields as $formField) {
                $fieldName = $formField->name;
                $isFileType = in_array($formField->type, ['file', 'photo', 'video', 'audio'], true);

                if ($isFileType) {
                    if (!$request->hasFile($fieldName)) {
                        continue;
                    }
                    $files = $request->file($fieldName);
                    if (!is_array($files)) {
                        $files = [$files];
                    }
                    $recordField = RecordField::where('record_id', $record->id)
                        ->where('form_field_id', $formField->id)
                        ->first();
                    $existingPaths = [];
                    if ($recordField && $recordField->value_json) {
                        $prev = $recordField->value_json;
                        if (is_array($prev) && isset($prev['value'])) {
                            $prev = $prev['value'];
                        }
                        $existingPaths = is_array($prev) ? $prev : ($prev ? [$prev] : []);
                    }
                    $existingSize = 0;
                    foreach ($record->files()->where('form_field_id', $formField->id)->get() as $f) {
                        $existingSize += $f->size ?? 0;
                    }
                    $newBytes = 0;
                    foreach ($files as $file) {
                        $newBytes += $file->getSize();
                    }
                    if ($existingSize + $newBytes > $maxFileBytes) {
                        return $this->errorResponse('Total size of files for "' . ($formField->label ?? $fieldName) . '" must not exceed 5MB.', 422);
                    }
                    $paths = [];
                    foreach ($files as $file) {
                        $paths[] = $this->formService->handleFileUpload($file, $formField, $record, $user);
                    }
                    $allPaths = array_merge($existingPaths, $paths);
                    $fieldValue = count($allPaths) === 1 ? $allPaths[0] : $allPaths;
                } else {
                    if (!$request->has($fieldName)) {
                        continue;
                    }
                    $fieldValue = $request->input($fieldName);
                }

                // Handle calculated fields
                if ($formField->type === 'calculated' && $formField->calculation_formula) {
                    $fieldValue = $this->formService->evaluateFormula($formField->calculation_formula, $request->all());
                }

                // Find existing record field or create new one
                $recordField = RecordField::where('record_id', $record->id)
                    ->where('form_field_id', $formField->id)
                    ->first();

                $valueToStore = is_array($fieldValue) ? $fieldValue : ['value' => $fieldValue];

                if ($recordField) {
                    $recordField->update(['value_json' => $valueToStore]);
                } else {
                    RecordField::create([
                        'tenant_id' => $user->tenant_id,
                        'record_id' => $record->id,
                        'form_field_id' => $formField->id,
                        'value_json' => $valueToStore,
                    ]);
                }
            }

            // Update record
            $record->update([
                'updated_by' => $user->id,
            ]);

            return $this->successResponse(
                new RecordResource($record->load('form', 'recordFields.formField', 'files')),
                'Form data updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update form data: ' . $e->getMessage());
        }
    }

}

