<?php

namespace App\Http\Resources\Api;

use App\Constants\RecordStatus;
use Illuminate\Http\Request;

class RecordResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form' => $this->whenLoaded('form', function () {
                return [
                    'id' => $this->form->id,
                    'name' => $this->form->name,
                ];
            }),
            'work_order' => $this->whenLoaded('workOrder', function () {
                return [
                    'id' => $this->workOrder->id,
                ];
            }),
            'status' => RecordStatus::getLabel($this->status),
            'submitted_by' => $this->whenLoaded('submittedBy', function () {
                return [
                    'id' => $this->submittedBy->id,
                    'name' => $this->submittedBy->name,
                ];
            }),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'location' => $this->location,
            'fields' => $this->whenLoaded('recordFields', function () {
                $fields = [];
                foreach ($this->recordFields as $recordField) {
                    if ($recordField->formField) {
                        $value = $recordField->value_json;
                        if (is_array($value) && isset($value['value'])) {
                            $value = $value['value'];
                        }
                        $fields[$recordField->formField->name] = $value;
                    }
                }
                return $fields;
            }),
            'files' => FileResource::collection($this->whenLoaded('files')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

