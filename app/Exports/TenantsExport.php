<?php

namespace App\Exports;

use App\Models\Tenant;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TenantsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Tenant::query()
            ->withCount('users')
            ->withCount('projects')
            ->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Company Name',
            'Field of Work',
            'Users Count',
            'Projects Count',
            'Storage Used (MB)',
            'Total Payments',
            'Status',
            'Created At',
            'Last Active',
        ];
    }

    public function map($tenant): array
    {
        return [
            $tenant->id,
            $tenant->company_name,
            $tenant->field_of_work ?? '',
            $tenant->users_count ?? 0,
            $tenant->projects_count ?? 0,
            $this->formatStorageSize($tenant->storage_used ?? 0),
            $this->formatCurrency($tenant->total_payments ?? 0),
            $this->getStatusLabel($tenant->status),
            $tenant->created_at->format('Y-m-d H:i:s'),
            $tenant->last_active_at ? $tenant->last_active_at->format('Y-m-d H:i:s') : 'Never',
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
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Suspended',
            3 => 'Cancelled',
            default => 'Unknown'
        };
    }

    private function formatStorageSize($bytes): string
    {
        if ($bytes === 0) {
            return '0.00';
        }

        $mb = $bytes / 1024 / 1024;
        return number_format($mb, 2);
    }

    private function formatCurrency($amount): string
    {
        return number_format($amount, 2);
    }
}
