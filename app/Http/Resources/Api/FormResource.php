<?php

namespace App\Http\Resources\Api;

use App\Constants\FormStatus;
use Illuminate\Http\Request;

class FormResource extends BaseResource
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => FormStatus::getLabel($this->status),
            'version' => $this->version,
            'schema_json' => $this->schema_json,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'fields' => FormFieldResource::collection($this->whenLoaded('formFields')),
            'fields_count' => $this->when(isset($this->formFields), function () {
                return $this->formFields->count();
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

