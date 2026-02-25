<?php

namespace App\Http\Requests\Api\WorkOrder;

use App\Http\Requests\Api\BaseApiRequest;
use App\Models\Form;
use App\Models\FormField;

class SubmitFormRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $formId = $this->input('form_id');
        if (!$formId) {
            return;
        }
        $form = Form::with('formFields')->find($formId);
        if (!$form) {
            return;
        }
        $data = $this->all();
        foreach ($form->formFields as $field) {
            if ($field->type !== 'multiselect' || !$this->has($field->name)) {
                continue;
            }
            $value = $this->input($field->name);
            if (!is_array($value)) {
                $data[$field->name] = $value === null || $value === '' ? [] : [$value];
            }
        }
        $this->merge($data);
    }

    public function rules(): array
    {
        $workOrderId = $this->route('workOrder');
        $formId = $this->input('form_id');

        if (!$formId) {
            return [];
        }

        $form = Form::with('formFields')->find($formId);

        if (!$form) {
            return [];
        }

        $rules = [
            'form_id' => 'required|exists:forms,id',
            'work_order_id' => 'required|exists:work_orders,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];

        // Build validation rules from form fields
        foreach ($form->formFields as $field) {
            $fieldRules = [];

            $config = is_array($field->config_json) ? $field->config_json : (array) $field->config_json;
            $isRequired = $config['required'] ?? false;

            if ($isRequired) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                case 'currency':
                    $fieldRules[] = 'numeric';
                    if ($field->min_value !== null) {
                        $fieldRules[] = 'min:' . $field->min_value;
                    }
                    if ($field->max_value !== null) {
                        $fieldRules[] = 'max:' . $field->max_value;
                    }
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'time':
                    $fieldRules[] = 'date_format:H:i';
                    break;
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                case 'photo':
                case 'image':
                case 'video':
                case 'audio':
                    $fieldRules[] = 'array';
                    if ($isRequired) {
                        $fieldRules[] = 'min:1';
                    }
                    $rules[$field->name] = $fieldRules;
                    $starRules = ['file'];
                    if ($field->type === 'photo' || $field->type === 'image') {
                        $starRules[] = 'image';
                        $starRules[] = 'max:5120';
                    } elseif ($field->type === 'video') {
                        $starRules[] = 'mimes:mp4,avi,mov';
                        $starRules[] = 'max:51200';
                    } elseif ($field->type === 'audio') {
                        $starRules[] = 'mimes:mp3,wav,ogg';
                        $starRules[] = 'max:10240';
                    } else {
                        $starRules[] = 'max:10240';
                    }
                    $rules[$field->name . '.*'] = $starRules;
                    continue 2;
                case 'select':
                case 'radio':
                    if (!empty($field->options)) {
                        $options = is_array($field->options) ? $field->options : json_decode($field->options, true);
                        if (is_array($options)) {
                            $fieldRules[] = 'in:' . implode(',', $options);
                        }
                    }
                    break;
                case 'multiselect':
                    $fieldRules[] = 'array';
                    if (!empty($field->options)) {
                        $options = is_array($field->options) ? $field->options : json_decode($field->options, true);
                        if (is_array($options)) {
                            $fieldRules[] = 'in:' . implode(',', $options);
                        }
                    }
                    break;
            }

            // Regex validation
            if (!empty($field->regex_pattern)) {
                $fieldRules[] = 'regex:' . $field->regex_pattern;
            }

            if (!empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $formId = $this->input('form_id');
        if (!$formId) {
            return [];
        }
        $form = Form::with('formFields')->find($formId);
        if (!$form) {
            return [];
        }
        $out = [];
        foreach ($form->formFields as $field) {
            $label = $field->label ?? str_replace('_', ' ', ucfirst($field->name));
            $out["{$field->name}.required"] = "$label is required.";
            if (in_array($field->type, ['file', 'photo', 'image', 'video', 'audio'], true)) {
                $out["{$field->name}.array"] = "$label must be one or more files.";
                $out["{$field->name}.min"] = "At least one file is required for $label.";
                $out["{$field->name}.*.file"] = "Each item in $label must be a file.";
            }
            $out["{$field->name}.date"] = "$label must be a valid date (e.g. Y-m-d).";
            $out["{$field->name}.date_format"] = "$label must match the required format.";
            $out["{$field->name}.numeric"] = "$label must be a number.";
            $out["{$field->name}.email"] = "$label must be a valid email address.";
            $out["{$field->name}.url"] = "$label must be a valid URL.";
            $out["{$field->name}.in"] = "$label must be one of the allowed values.";
            $out["{$field->name}.min"] = "$label must be at least :min.";
            $out["{$field->name}.max"] = "$label must not exceed :max.";
            $out["{$field->name}.regex"] = "$label format is invalid.";
        }
        return $out;
    }
}

