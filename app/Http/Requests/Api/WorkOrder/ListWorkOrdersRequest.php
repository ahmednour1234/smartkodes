<?php

namespace App\Http\Requests\Api\WorkOrder;

use App\Http\Requests\Api\BaseApiRequest;

class ListWorkOrdersRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|integer|in:0,1,2,3',
            'priority' => 'nullable|integer',
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'radius' => 'nullable|numeric|min:0.1|max:100',
            'sort_by' => 'nullable|string|in:priority,due_date,distance,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}

