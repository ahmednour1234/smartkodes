<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrdersColumnGuideSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Column Guide';
    }

    public function array(): array
    {
        return [
            ['project_code', 'Required. The project CODE (e.g. PRJ-001), not the project name or ID. Find codes in Projects list.'],
            ['title', 'Required. Work order title (e.g. "Site inspection - Building A").'],
            ['assigned_to_email', 'Optional. Email of the user to assign. Must be a user in your organization. Leave empty for unassigned.'],
            ['status', 'Optional. One of: Draft, Assigned, In Progress, Completed. Default: Draft.'],
            ['importance_level', 'Optional. One of: low, medium, high, critical.'],
            ['due_date', 'Optional. Due date in Y-m-d format (e.g. 2026-12-31).'],
            ['priority_value', 'Optional. Number (e.g. 2). Use with priority_unit for SLA.'],
            ['priority_unit', 'Optional. One of: hour, day, week, month. Use with priority_value.'],
            ['latitude', 'Optional. Location latitude (e.g. 25.276987).'],
            ['longitude', 'Optional. Location longitude (e.g. 55.296249).'],
            ['description', 'Optional. Free text description.'],
        ];
    }

    public function headings(): array
    {
        return ['Column', 'Description'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
