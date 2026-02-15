<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class FormFieldResource extends BaseResource
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
            'type' => $this->type,
            'label' => $this->config_json['label'] ?? $this->name,
            'placeholder' => $this->placeholder ?? $this->config_json['placeholder'] ?? null,
            'required' => $this->config_json['required'] ?? false,
            'order' => $this->order,
            'default_value' => $this->default_value,
            'config' => $this->config_json,
            'validation' => [
                'min_value' => $this->min_value,
                'max_value' => $this->max_value,
                'regex_pattern' => $this->regex_pattern,
            ],
            'options' => $this->options ?? (is_array($this->config_json['options'] ?? null) ? $this->config_json['options'] : null),
            'is_sensitive' => $this->is_sensitive,
        ];
    }
}

