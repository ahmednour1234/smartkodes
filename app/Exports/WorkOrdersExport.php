<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = WorkOrder::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['project', 'user', 'forms']);

        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['priority']) && $this->filters['priority'] !== '') {
            $query->where('priority', $this->filters['priority']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Project',
            'Title',
            'Assigned To',
            'Status',
            'Priority',
            'Due Date',
            'Completed At',
            'Forms Count',
            'Location',
            'Created At',
        ];
    }

    public function map($workOrder): array
    {
        return [
            $workOrder->id,
            $workOrder->project ? $workOrder->project->name : '',
            $workOrder->title,
            $workOrder->user ? $workOrder->user->name : '',
            $this->getStatusLabel($workOrder->status),
            $this->getPriorityLabel($workOrder->priority),
            $workOrder->due_date ? $workOrder->due_date->format('Y-m-d') : '',
            $workOrder->completed_at ? $workOrder->completed_at->format('Y-m-d H:i:s') : '',
            $workOrder->forms->count(),
            $workOrder->location ?? '',
            $workOrder->created_at->format('Y-m-d H:i:s'),
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
            1 => 'Open',
            2 => 'In Progress',
            3 => 'Completed',
            4 => 'Cancelled',
            default => 'Unknown'
        };
    }

    private function getPriorityLabel($priority): string
    {
        return match ($priority) {
            0 => 'Low',
            1 => 'Medium',
            2 => 'High',
            3 => 'Critical',
            default => 'Medium'
        };
    }
}
