<?php

namespace App\Exports;

use App\Models\Record;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecordsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tenantId;
    protected $filters;

    public function __construct($tenantId, $filters = [])
    {
        $this->tenantId = $tenantId;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Record::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['workOrder', 'form', 'submittedBy']);

        if (!empty($this->filters['work_order_id'])) {
            $query->where('work_order_id', $this->filters['work_order_id']);
        }

        if (!empty($this->filters['form_id'])) {
            $query->where('form_id', $this->filters['form_id']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('submitted_by', $this->filters['user_id']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Work Order',
            'Form',
            'Submitted By',
            'Status',
            'Data Fields Count',
            'Images Count',
            'Videos Count',
            'Files Count',
            'Location',
            'Submitted At',
        ];
    }

    public function map($record): array
    {
        $data = is_string($record->data) ? json_decode($record->data, true) : $record->data;
        $mediaCount = $this->getMediaCounts($data);

        return [
            $record->id,
            $record->workOrder ? $record->workOrder->title : '',
            $record->form ? $record->form->name : '',
            $record->submittedBy?->name ?? '',
            $this->getStatusLabel($record->status),
            count($data ?? []),
            $mediaCount['images'],
            $mediaCount['videos'],
            $mediaCount['files'],
            $record->location ?? '',
            $record->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($status): string
    {
        return match ($status) {
            0 => 'Draft',
            1 => 'Submitted',
            2 => 'Reviewed',
            3 => 'Approved',
            4 => 'Rejected',
            default => 'Draft'
        };
    }

    private function getMediaCounts($data): array
    {
        $counts = [
            'images' => 0,
            'videos' => 0,
            'files' => 0,
        ];

        if (!is_array($data)) {
            return $counts;
        }

        foreach ($data as $value) {
            if (is_string($value)) {
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value)) {
                    $counts['images']++;
                } elseif (preg_match('/\.(mp4|mov|avi|mkv|webm)$/i', $value)) {
                    $counts['videos']++;
                } elseif (preg_match('/\.(pdf|doc|docx|xls|xlsx|txt)$/i', $value)) {
                    $counts['files']++;
                }
            } elseif (is_array($value)) {
                // Handle nested arrays (e.g., multiple file uploads)
                $nestedCounts = $this->getMediaCounts($value);
                $counts['images'] += $nestedCounts['images'];
                $counts['videos'] += $nestedCounts['videos'];
                $counts['files'] += $nestedCounts['files'];
            }
        }

        return $counts;
    }
}
