<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\RecordResource;
use App\Models\Record;
use App\Services\ApiResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                ->with(['form', 'workOrder', 'recordFields.formField']);

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
     * Export a record as PDF (form filled with data). Only own records.
     */
    public function pdf(string $recordId)
    {
        $user = Auth::user();
        $record = Record::where('submitted_by', $user->id)
            ->where('id', $recordId)
            ->with(['form', 'workOrder', 'recordFields.formField'])
            ->firstOrFail();

        $formName = $record->form?->name ?? 'Form';
        $submittedAt = $record->submitted_at?->format('Y-m-d H:i') ?? '—';
        $woId = $record->workOrder?->id ?? '—';

        $rows = '';
        foreach ($record->recordFields as $rf) {
            if (!$rf->formField) {
                continue;
            }
            $label = e($rf->formField->label ?? $rf->formField->name);
            $val = $rf->value_json;
            if (is_array($val) && isset($val['value'])) {
                $val = $val['value'];
            }
            $value = is_scalar($val) ? e((string) $val) : e(json_encode($val));
            $rows .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>{$label}</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$value}</td></tr>";
        }

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;padding:16px;} table{width:100%;border-collapse:collapse;} h1{font-size:18px;margin-bottom:8px;} .meta{color:#666;margin-bottom:16px;}</style></head>
        <body>
        <h1>{$formName}</h1>
        <p class="meta">Submitted: {$submittedAt} &nbsp;|&nbsp; Work Order: {$woId}</p>
        <table><tbody>{$rows}</tbody></table>
        </body>
        </html>
        HTML;

        $filename = 'form_' . $recordId . '_' . date('Y-m-d') . '.pdf';
        return Pdf::loadHtml($html)->download($filename);
    }
}
