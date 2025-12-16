<?php

namespace App\Http\Resources\Api;

use App\Constants\WorkOrderStatus;
use Illuminate\Http\Request;

class WorkOrderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'code' => $this->project->code,
                ];
            }),
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ];
            }),
            'status' => $this->status,
            'status_label' => WorkOrderStatus::getLabel($this->status),
            'priority_value' => $this->priority_value,
            'priority_unit' => $this->priority_unit,
            'due_date' => $this->due_date?->toIso8601String(),
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'forms' => FormResource::collection($this->whenLoaded('forms')),
            'records_count' => $this->when(isset($this->records_count), $this->records_count),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        // Add distance if calculated
        if (isset($this->distance)) {
            $data['distance'] = round($this->distance, 2);
            $data['distance_unit'] = 'km';
        }

        return $data;
    }
}

