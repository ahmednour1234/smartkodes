<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrdersTemplateSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Import';
    }

    public function headings(): array
    {
        return [
            'project_code',
            'title',
            'assigned_to_email',
            'status',
            'importance_level',
            'due_date',
            'priority_value',
            'priority_unit',
            'latitude',
            'longitude',
            'description',
        ];
    }

    public function collection()
    {
        return collect([[
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ]]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
