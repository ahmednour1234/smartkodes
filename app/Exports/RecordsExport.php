<?php

namespace App\Exports;

use App\Models\Record;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecordsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $tenantId;
    protected $filters;
    protected $formFields = null;

    public function __construct($tenantId, $filters = [])
    {
        $this->tenantId = $tenantId;
        $this->filters = $filters;
        if (!empty($filters['form_id'])) {
            $form = \App\Models\Form::where('tenant_id', $tenantId)->with(['formFields' => fn ($q) => $q->orderBy('order')])->find($filters['form_id']);
            $this->formFields = $form ? $form->formFields : collect();
        }
    }

    public function query()
    {
        $query = Record::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['workOrder', 'form.formFields', 'submittedBy', 'recordFields.formField', 'files']);

        if (!empty($this->filters['work_order_id'])) {
            $query->where('work_order_id', $this->filters['work_order_id']);
        }

        if (!empty($this->filters['form_id'])) {
            $query->where('form_id', $this->filters['form_id']);
        }

        if (!empty($this->filters['record_ids'])) {
            $query->whereIn('id', $this->filters['record_ids']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('submitted_by', $this->filters['user_id']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        $base = ['ID', 'Work Order', 'Form', 'Submitted By', 'Status', 'Submitted At', 'Location'];
        if ($this->formFields && $this->formFields->isNotEmpty()) {
            $labels = $this->formFields->map(fn ($f) => $f->label ?? $f->name)->toArray();
            return array_merge($base, $labels);
        }
        return $base;
    }

    public function map($record): array
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $locationStr = '';
        if (is_array($record->location) && isset($record->location['latitude'], $record->location['longitude'])) {
            $locationStr = $record->location['latitude'] . ', ' . $record->location['longitude'];
        } elseif (!empty($record->location)) {
            $locationStr = is_string($record->location) ? $record->location : json_encode($record->location);
        }

        $row = [
            $record->id,
            $record->workOrder ? $record->workOrder->title : '',
            $record->form ? $record->form->name : '',
            $record->submittedBy?->name ?? '',
            $this->getStatusLabel($record->status),
            $record->submitted_at?->format('Y-m-d H:i:s') ?? $record->created_at->format('Y-m-d H:i:s'),
            $locationStr,
        ];

        if ($this->formFields && $this->formFields->isNotEmpty()) {
            foreach ($this->formFields as $field) {
                $recordField = $record->recordFields->firstWhere('form_field_id', $field->id);
                $value = '';
                if (in_array($field->type, ['photo', 'video', 'audio', 'file'], true)) {
                    $recordFiles = $record->files->where('form_field_id', $field->id);
                    $urls = [];
                    foreach ($recordFiles as $file) {
                        $path = $file->path ?? $file->getRawOriginal('path');
                        if ($path) {
                            $path = ltrim($path, '/');
                            $urls[] = $baseUrl . '/storage/' . $path;
                        }
                    }
                    $value = implode("\n", $urls);
                } elseif ($recordField && $recordField->value_json !== null) {
                    $v = $recordField->value_json;
                    if (is_array($v) && isset($v['value'])) {
                        $value = is_array($v['value']) ? implode(', ', $v['value']) : (string) $v['value'];
                    } elseif (is_array($v)) {
                        $value = implode(', ', $v);
                    } else {
                        $value = (string) $v;
                    }
                }
                $row[] = $value;
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxRow = $sheet->getHighestRow();
                $maxCol = $sheet->getHighestColumn();
                $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);
                for ($row = 2; $row <= $maxRow; $row++) {
                    for ($col = 1; $col <= $maxColIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $val = $cell->getValue();
                        if (is_string($val) && (str_starts_with($val, 'http://') || str_starts_with($val, 'https://'))) {
                            $url = str_contains($val, "\n") ? trim(explode("\n", $val)[0]) : $val;
                            $cell->getHyperlink()->setUrl($url);
                            $cell->getStyle()->getFont()->setUnderline(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0563C1'));
                        }
                    }
                }
            },
        ];
    }

    private function getStatusLabel($status): string
    {
        return match ((int) $status) {
            0 => 'Draft',
            1 => 'Submitted',
            2 => 'Reviewed',
            3 => 'Approved',
            4 => 'Rejected',
            default => 'Draft'
        };
    }
}
