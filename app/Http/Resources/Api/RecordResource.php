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
                $fileFieldTypes = ['file', 'photo', 'video', 'audio', 'voice_message', 'image'];
                foreach ($this->recordFields as $recordField) {
                    if (!$recordField->formField) {
                        continue;
                    }
                    $name = $recordField->formField->name;
                    $isFileType = in_array($recordField->formField->type, $fileFieldTypes, true);
                    if ($isFileType && $this->relationLoaded('files')) {
                        $paths = $this->files
                            ->where('form_field_id', $recordField->form_field_id)
                            ->pluck('path')
                            ->values()
                            ->all();
                        $fields[$name] = count($paths) === 1 ? $paths[0] : $paths;
                    } else {
                        $value = $recordField->value_json;
                        if (is_array($value) && isset($value['value'])) {
                            $value = $value['value'];
                        }
                        $fields[$name] = $value;
                    }
                }
                if ($this->relationLoaded('files')) {
                    $groupedFiles = [];
                    foreach ($this->files as $file) {
                        if (!$file->formField) {
                            continue;
                        }
                        $fieldName = $file->formField->name;
                        $groupedFiles[$fieldName] ??= [];
                        $groupedFiles[$fieldName][] = $file->path;
                    }
                    foreach ($groupedFiles as $fieldName => $paths) {
                        $fields[$fieldName] = count($paths) === 1 ? $paths[0] : array_values($paths);
                    }
                }
                return $fields;
            }),
            'field_labels' => $this->whenLoaded('recordFields', function () {
                $labels = [];
                foreach ($this->recordFields as $recordField) {
                    if (!$recordField->formField) {
                        continue;
                    }
                    $config = is_array($recordField->formField->config_json) ? $recordField->formField->config_json : [];
                    $labels[$recordField->formField->name] = $config['label'] ?? $recordField->formField->name;
                }
                if ($this->relationLoaded('files')) {
                    foreach ($this->files as $file) {
                        if (!$file->formField) {
                            continue;
                        }
                        $config = is_array($file->formField->config_json) ? $file->formField->config_json : [];
                        $labels[$file->formField->name] = $config['label'] ?? $file->formField->name;
                    }
                }
                return $labels;
            }),
            'files' => FileResource::collection($this->whenLoaded('files')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

