<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WorkOrdersTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new WorkOrdersTemplateSheet(),
            new WorkOrdersColumnGuideSheet(),
        ];
    }
}
