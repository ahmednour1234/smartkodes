<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function query()
    {
        return Project::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['creator', 'managers', 'fieldUsers'])
            ->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Project Name',
            'Code',
            'Client',
            'Area',
            'Status',
            'Start Date',
            'End Date',
            'Description',
            'Managers',
            'Field Users',
            'Created By',
            'Created At',
        ];
    }

    public function map($project): array
    {
        return [
            $project->id,
            $project->name,
            $project->code,
            $project->client_name,
            $project->area,
            $this->getStatusLabel($project->status),
            $project->start_date ? $project->start_date->format('Y-m-d') : '',
            $project->end_date ? $project->end_date->format('Y-m-d') : '',
            $project->description,
            $project->managers->pluck('name')->join(', '),
            $project->fieldUsers->pluck('name')->join(', '),
            $project->creator ? $project->creator->name : '',
            $project->created_at->format('Y-m-d H:i:s'),
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
            0 => 'Archived',
            1 => 'Active',
            2 => 'Paused',
            3 => 'Draft',
            default => 'Unknown'
        };
    }
}
