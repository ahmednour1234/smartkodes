<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkOrdersImport implements ToCollection, WithHeadingRow
{
    public array $succeeded = [];
    public array $failed = [];

    public function __construct(
        protected string $tenantId,
        protected string $createdBy
    ) {}

    public function collection(Collection $rows): void
    {
        $rowNumber = 2;
        foreach ($rows as $row) {
            $projectCode = $this->value($row, 'project_code');
            $title = $this->value($row, 'title');
            $ident = trim($title ?: '') . ($projectCode ? ' (' . $projectCode . ')' : '');

            if (empty($projectCode) || empty($title)) {
                $this->failed[] = ['row' => $rowNumber, 'ident' => $ident ?: '—', 'reason' => 'Missing project_code or title'];
                $rowNumber++;
                continue;
            }

            $project = Project::where('tenant_id', $this->tenantId)
                ->where('code', $projectCode)
                ->first();
            if (!$project) {
                $this->failed[] = ['row' => $rowNumber, 'ident' => $ident, 'reason' => 'Project not found for code: ' . $projectCode];
                $rowNumber++;
                continue;
            }

            $assignedTo = null;
            $email = $this->value($row, 'assigned_to_email');
            if (!empty($email)) {
                $user = User::where('tenant_id', $this->tenantId)
                    ->where('email', $email)
                    ->first();
                if ($user) {
                    $assignedTo = $user->id;
                }
            }

            $status = $this->parseStatus($this->value($row, 'status'));
            $importanceLevel = $this->parseImportance($this->value($row, 'importance_level'));
            $dueDate = $this->parseDate($this->value($row, 'due_date'));
            $priorityValue = $this->parseInt($row, 'priority_value');
            $priorityUnit = $this->value($row, 'priority_unit');
            if (!in_array($priorityUnit, ['hour', 'day', 'week', 'month'], true)) {
                $priorityUnit = null;
            }
            $latitude = $this->parseFloat($row, 'latitude');
            $longitude = $this->parseFloat($row, 'longitude');
            $description = $this->value($row, 'description');

            try {
                WorkOrder::create([
                    'tenant_id' => $this->tenantId,
                    'title' => $title,
                    'project_id' => $project->id,
                    'assigned_to' => $assignedTo,
                    'status' => $status,
                    'importance_level' => $importanceLevel,
                    'due_date' => $dueDate,
                    'priority_value' => $priorityValue,
                    'priority_unit' => $priorityUnit,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'description' => $description ?: null,
                    'created_by' => $this->createdBy,
                    'updated_by' => $this->createdBy,
                ]);
                $this->succeeded[] = ['row' => $rowNumber, 'ident' => $ident];
            } catch (\Throwable $e) {
                $this->failed[] = ['row' => $rowNumber, 'ident' => $ident, 'reason' => $e->getMessage()];
            }
            $rowNumber++;
        }
    }

    private function value(Collection $row, string $key): ?string
    {
        $v = $row[$key] ?? $row[\Str::slug($key, '_')] ?? null;
        if ($v === null || $v === '') {
            return null;
        }
        return trim((string) $v);
    }

    private function parseStatus(?string $v): int
    {
        if (empty($v)) {
            return 0;
        }
        $v = strtolower(trim($v));
        return match ($v) {
            'draft' => 0,
            'assigned', 'open' => 1,
            'in progress', 'in_progress' => 2,
            'completed', 'complete' => 3,
            default => 0,
        };
    }

    private function parseImportance(?string $v): ?string
    {
        if (empty($v)) {
            return null;
        }
        $v = strtolower(trim($v));
        return in_array($v, ['low', 'medium', 'high', 'critical'], true) ? $v : null;
    }

    private function parseDate(?string $v): ?string
    {
        if (empty($v)) {
            return null;
        }
        $v = trim($v);
        $time = strtotime($v);
        return $time ? date('Y-m-d', $time) : null;
    }

    private function parseInt(Collection $row, string $key): ?int
    {
        $v = $this->value($row, $key);
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (int) $v : null;
    }

    private function parseFloat(Collection $row, string $key): ?float
    {
        $v = $this->value($row, $key);
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (float) $v : null;
    }
}
