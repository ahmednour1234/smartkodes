<?php

namespace App\Exports;

use App\Models\Form;
use App\Models\Record;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tenantId;
    protected array $filters;

    public function __construct($tenantId, array $filters = [])
    {
        $this->tenantId = $tenantId;
        $this->filters  = $filters;
    }

    /**
     * Query used by Maatwebsite Excel.
     */
    public function query()
    {
        $query = Form::query()
            ->where('tenant_id', $this->tenantId);

        // فلتر الحالة لو موجودة
        if (isset($this->filters['status']) && $this->filters['status'] !== '' && $this->filters['status'] !== null) {
            $query->where('status', $this->filters['status']);
        }

        // فلتر الكاتيجوري لو موجود
        if (isset($this->filters['category_id']) && $this->filters['category_id'] !== '' && $this->filters['category_id'] !== null) {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query->latest();
    }

    /**
     * Excel headings.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Version',
            'Status',
            'Fields Count',
            'Records Count',
            'Last Modified',
            'Created At',
        ];
    }

    /**
     * Row mapping.
     */
    public function map($form): array
    {
        // في الجدول عندك العمود اسمه schema_json (مع fallback لو الاسم مختلف)
        $schemaRaw = $form->schema_json ?? $form->schema ?? null;

        if (is_string($schemaRaw)) {
            $schema = json_decode($schemaRaw, true) ?: [];
        } elseif (is_array($schemaRaw)) {
            $schema = $schemaRaw;
        } else {
            $schema = [];
        }

        $fieldsCount = isset($schema['fields']) && is_array($schema['fields'])
            ? count($schema['fields'])
            : 0;

        // نحسب عدد الـ records يدويًا
        $recordsCount = Record::where('tenant_id', $this->tenantId)
            ->where('form_id', $form->id)
            ->count();

        return [
            $form->id,
            $form->name,
            $form->version ?? '1.0',
            $this->getStatusLabel($form->status),
            $fieldsCount,
            $recordsCount,
            optional($form->updated_at)->format('Y-m-d H:i:s'),
            optional($form->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Style for Excel sheet (header bold).
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Status label helper.
     */
    private function getStatusLabel($status): string
    {
        return match ((int) $status) {
            0 => 'Draft',
            1 => 'Active',
            2 => 'Archived',
            default => 'Draft',
        };
    }
}
