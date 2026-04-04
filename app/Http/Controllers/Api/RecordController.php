<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\RecordResource;
use App\Models\Record;
use App\Services\ApiResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordController extends BaseApiController
{
    public function __construct(ApiResponseService $response)
    {
        parent::__construct($response);
    }

    /**
     * List records submitted by the current user (field worker).
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = (int) $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 50);

            $query = Record::where('submitted_by', $user->id)
                ->with(['form', 'workOrder', 'recordFields.formField', 'files.formField']);

            if ($request->filled('work_order_id')) {
                $query->where('work_order_id', $request->work_order_id);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $records = $query->orderByDesc('submitted_at')->paginate($perPage);

            return $this->paginatedResponse(
                RecordResource::collection($records),
                'Records retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve records: ' . $e->getMessage());
        }
    }

    /**
     * Get a single record by ID (own records only).
     */
    public function show(string $recordId)
    {
        $user = Auth::user();
        $record = Record::where('submitted_by', $user->id)
            ->where('id', $recordId)
            ->with(['form', 'workOrder', 'recordFields.formField', 'files.formField'])
            ->firstOrFail();

        return $this->successResponse(
            new RecordResource($record),
            'Record retrieved successfully'
        );
    }

    /**
     * Export a record as PDF (form filled with data). Only own records.
     */
    public function pdf(Request $request, string $recordId)
    {
        $user = Auth::user();
        $record = Record::where('submitted_by', $user->id)
            ->where('id', $recordId)
            ->with(['form', 'workOrder', 'recordFields.formField', 'files.formField'])
            ->firstOrFail();

        $formatValue = static function ($value): string {
            if (is_array($value) && array_key_exists('value', $value)) {
                $value = $value['value'];
            }
            if (is_array($value)) {
                $flat = array_map(
                    static fn ($item) => is_scalar($item) || $item === null
                        ? (string) $item
                        : json_encode($item, JSON_UNESCAPED_UNICODE),
                    $value
                );
                return implode(', ', array_filter($flat, static fn ($v) => trim((string) $v) !== ''));
            }
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
            if ($value === null || $value === '') {
                return '-';
            }
            return (string) $value;
        };

        $fieldRows = [];
        foreach ($record->recordFields as $rf) {
            if (!$rf->formField) {
                continue;
            }
            $fieldRows[] = [
                'label' => $rf->formField->label ?? $rf->formField->name,
                'value' => $formatValue($rf->value_json),
            ];
        }

        $fileRows = [];
        foreach ($record->files as $file) {
            $path = $file->path ?? '';
            $url = '';
            if ($path !== '') {
                $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
                    ? $path
                    : asset(Storage::url($path));
            }
            $fileRows[] = [
                'field' => $file->formField?->label ?? $file->formField?->name ?? 'Attachment',
                'name' => $file->name ?: basename($path),
                'url' => $url,
                'mime' => $file->mime_type,
            ];
        }

        $pdf = Pdf::loadView('pdf.record_web', [
            'formName' => $record->form?->name ?? 'Form',
            'recordId' => $recordId,
            'submittedAt' => $record->submitted_at?->format('Y-m-d H:i') ?? '-',
            'workOrderId' => $record->workOrder?->id ?? '-',
            'fieldRows' => $fieldRows,
            'fileRows' => $fileRows,
            'mode' => $request->query('mode', 'web'),
        ]);

        $filename = 'form_' . $recordId . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
