<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\WorkOrder\ListWorkOrdersRequest;
use App\Http\Requests\Api\WorkOrder\SubmitFormRequest;
use App\Http\Resources\Api\WorkOrderResource;
use App\Http\Resources\Api\FormResource;
use App\Http\Resources\Api\RecordResource;
use App\Repositories\WorkOrderRepository;
use App\Repositories\FormRepository;
use App\Services\ApiResponseService;
use App\Services\WorkOrderService;
use App\Constants\WorkOrderStatus;
use App\Constants\RecordStatus;
use App\Models\Notification;
use App\Models\Record;
use App\Models\RecordField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkOrderController extends BaseApiController
{
    protected WorkOrderRepository $workOrderRepository;
    protected FormRepository $formRepository;
    protected WorkOrderService $workOrderService;

    public function __construct(
        ApiResponseService $response,
        WorkOrderRepository $workOrderRepository,
        FormRepository $formRepository,
        WorkOrderService $workOrderService
    ) {
        parent::__construct($response);
        $this->workOrderRepository = $workOrderRepository;
        $this->formRepository = $formRepository;
        $this->workOrderService = $workOrderService;
    }

    /**
     * Get work orders assigned to current user with filters
     */
    public function index(ListWorkOrdersRequest $request)
    {
        try {
            $user = Auth::user();
            $filters = $request->validated();
            $perPage = $request->get('per_page', 15);

            $workOrders = $this->workOrderRepository->getAssignedToUser($user->id, $filters, $perPage);

            return $this->paginatedResponse(
                WorkOrderResource::collection($workOrders),
                'Work orders retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve work orders: ' . $e->getMessage());
        }
    }

    /**
     * Get single work order with map information
     */
    public function show(string $id, Request $request)
    {
        try {
            $user = Auth::user();
            $workOrder = $this->workOrderRepository->getWithMapInfo($id);

            // Verify work order is assigned to user
            if ($workOrder->assigned_to !== $user->id) {
                return $this->forbiddenResponse('You are not assigned to this work order');
            }

            $data = new WorkOrderResource($workOrder);

            // Add map information if location provided
            if ($workOrder->latitude && $workOrder->longitude) {
                $mapData = [
                    'destination' => [
                        'latitude' => $workOrder->latitude,
                        'longitude' => $workOrder->longitude,
                    ],
                    'google_maps_url' => $this->workOrderService->getGoogleMapsUrl($workOrder->latitude, $workOrder->longitude),
                    'google_maps_directions_url' => $this->workOrderService->getGoogleMapsDirectionsUrl($workOrder->latitude, $workOrder->longitude),
                ];

                // Calculate distance and estimated time if current location provided
                if ($request->has('current_latitude') && $request->has('current_longitude')) {
                    $currentLat = $request->get('current_latitude');
                    $currentLon = $request->get('current_longitude');

                    $distance = $this->workOrderRepository->calculateDistance(
                        $currentLat,
                        $currentLon,
                        $workOrder->latitude,
                        $workOrder->longitude
                    );

                    $estimatedTime = $this->workOrderRepository->getEstimatedTime($distance);

                    $mapData['current_location'] = [
                        'latitude' => $currentLat,
                        'longitude' => $currentLon,
                    ];
                    $mapData['distance'] = [
                        'value' => $distance,
                        'unit' => 'km',
                    ];
                    $mapData['estimated_time'] = $estimatedTime;
                }

                $data = $data->toArray($request);
                $data['map'] = $mapData;
            }

            return $this->successResponse($data, 'Work order retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Work order not found');
        }
    }

    /**
     * Get form details for a work order
     */
    public function getForm(string $workOrderId, string $formId)
    {
        try {
            $user = Auth::user();
            $workOrder = $this->workOrderRepository->find($workOrderId);

            // Verify work order is assigned to user
            if ($workOrder->assigned_to !== $user->id) {
                return $this->forbiddenResponse('You are not assigned to this work order');
            }

            // Verify form is associated with work order
            $form = $workOrder->forms()->where('forms.id', $formId)->first();

            if (!$form) {
                return $this->notFoundResponse('Form not found in this work order');
            }

            // Load form with fields
            $form = $this->formRepository->getForSubmission($formId);

            return $this->successResponse(
                new FormResource($form),
                'Form retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->notFoundResponse('Form not found');
        }
    }

    /**
     * Submit form data for a work order
     */
    public function submitForm(SubmitFormRequest $request, string $workOrderId)
    {
        try {
            $user = Auth::user();
            $workOrder = $this->workOrderRepository->find($workOrderId);

            // Verify work order is assigned to user
            if ($workOrder->assigned_to !== $user->id) {
                return $this->forbiddenResponse('You are not assigned to this work order');
            }

            $validated = $request->validated();
            $formId = $validated['form_id'];

            // Verify form is associated with work order
            $form = $workOrder->forms()->where('forms.id', $formId)->first();

            if (!$form) {
                return $this->notFoundResponse('Form not found in this work order');
            }

            // Load form with fields
            $form = $this->formRepository->getForSubmission($formId);

            // Get current form version or create one
            $formVersion = $form->formVersions()->latest()->first();
            if (!$formVersion) {
                $formVersion = $form->formVersions()->create([
                    'tenant_id' => $user->tenant_id,
                    'form_id' => $form->id,
                    'version' => $form->version,
                    'schema_json' => $form->schema_json,
                    'created_by' => $user->id,
                ]);
            }

            // Create record
            $record = Record::create([
                'tenant_id' => $user->tenant_id,
                'project_id' => $workOrder->project_id,
                'form_id' => $form->id,
                'form_version_id' => $formVersion->id,
                'work_order_id' => $workOrder->id,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
                'location' => [
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                ],
                'ip_address' => $request->ip(),
                'status' => RecordStatus::SUBMITTED,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $maxFileBytes = 5 * 1024 * 1024; // 5MB

            // Process form fields and store values
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
                    $totalBytes = 0;
                    foreach ($files as $file) {
                        $totalBytes += $file->getSize();
                    }
                    if ($totalBytes > $maxFileBytes) {
                        return $this->errorResponse('Total size of files for "' . ($formField->label ?? $fieldName) . '" must not exceed 5MB.', 422);
                    }
                    $paths = [];
                    foreach ($files as $file) {
                        $paths[] = $this->workOrderService->handleFileUpload($file, $formField, $record, $user);
                    }
                    $fieldValue = count($paths) === 1 ? $paths[0] : $paths;
                } else {
                    if (!$request->has($fieldName)) {
                        continue;
                    }
                    $fieldValue = $request->input($fieldName);
                }

                // Handle calculated fields
                if ($formField->type === 'calculated' && $formField->calculation_formula) {
                    $fieldValue = $this->workOrderService->evaluateFormula($formField->calculation_formula, $request->all());
                }

                // Store field value
                RecordField::create([
                    'tenant_id' => $user->tenant_id,
                    'record_id' => $record->id,
                    'form_field_id' => $formField->id,
                    'value_json' => is_array($fieldValue) ? $fieldValue : ['value' => $fieldValue],
                ]);
            }

            // Update work order status if needed
            if ($workOrder->status < WorkOrderStatus::IN_PROGRESS) {
                $workOrder->update(['status' => WorkOrderStatus::IN_PROGRESS]);
            }

            $workOrder->load('project.managers');
            $recordUrl = url('/tenant/records/' . $record->id);
            if (\Illuminate\Support\Facades\Route::has('tenant.records.show')) {
                $recordUrl = route('tenant.records.show', $record->id);
            }
            $formName = $form->name ?? 'Form';
            foreach ($workOrder->project->managers ?? [] as $manager) {
                if ($manager->id && $manager->id != $user->id) {
                    Notification::create([
                        'tenant_id' => $user->tenant_id,
                        'user_id' => $manager->id,
                        'type' => 'form',
                        'title' => 'New form submission',
                        'message' => "New submission for \"{$formName}\" in project: " . ($workOrder->project->name ?? ''),
                        'data' => ['record_id' => $record->id],
                        'action_url' => $recordUrl,
                        'created_by' => $user->id,
                    ]);
                }
            }

            return $this->createdResponse(
                new RecordResource($record->load('form', 'workOrder', 'recordFields.formField', 'files')),
                'Form submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit form: ' . $e->getMessage());
        }
    }

    /**
     * Get Google Maps URL for destination
     */
    public function getMapUrl(string $id, Request $request)
    {
        try {
            $user = Auth::user();
            $workOrder = $this->workOrderRepository->find($id);

            if ($workOrder->assigned_to !== $user->id) {
                return $this->forbiddenResponse('You are not assigned to this work order');
            }

            if (!$workOrder->latitude || !$workOrder->longitude) {
                return $this->errorResponse('Work order does not have a location', 400);
            }

            $url = $this->workOrderService->getGoogleMapsUrl($workOrder->latitude, $workOrder->longitude);

            return $this->successResponse([
                'url' => $url,
                'destination' => [
                    'latitude' => $workOrder->latitude,
                    'longitude' => $workOrder->longitude,
                ],
            ], 'Map URL generated successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Work order not found');
        }
    }

    /**
     * Get Google Maps directions URL
     */
    public function getDirectionsUrl(string $id, Request $request)
    {
        try {
            $user = Auth::user();
            $workOrder = $this->workOrderRepository->find($id);

            if ($workOrder->assigned_to !== $user->id) {
                return $this->forbiddenResponse('You are not assigned to this work order');
            }

            if (!$workOrder->latitude || !$workOrder->longitude) {
                return $this->errorResponse('Work order does not have a location', 400);
            }

            $currentLat = $request->get('latitude');
            $currentLon = $request->get('longitude');

            if (!$currentLat || !$currentLon) {
                // Return URL without origin (user's current location will be used)
                $url = $this->workOrderService->getGoogleMapsDirectionsUrl($workOrder->latitude, $workOrder->longitude);
            } else {
                $url = $this->workOrderService->getGoogleMapsDirectionsUrl($workOrder->latitude, $workOrder->longitude, $currentLat, $currentLon);
            }

            return $this->successResponse([
                'url' => $url,
                'destination' => [
                    'latitude' => $workOrder->latitude,
                    'longitude' => $workOrder->longitude,
                ],
            ], 'Directions URL generated successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Work order not found');
        }
    }

}

