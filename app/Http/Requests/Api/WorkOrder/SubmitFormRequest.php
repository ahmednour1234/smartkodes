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
                case 'video':
                case 'audio':
                    $fieldRules[] = 'file';
                    if ($field->type === 'photo') {
                        $fieldRules[] = 'image';
                        $fieldRules[] = 'max:5120'; // 5MB
                    } elseif ($field->type === 'video') {
                        $fieldRules[] = 'mimes:mp4,avi,mov';
                        $fieldRules[] = 'max:51200'; // 50MB
                    } elseif ($field->type === 'audio') {
                        $fieldRules[] = 'mimes:mp3,wav,ogg';
                        $fieldRules[] = 'max:10240'; // 10MB
                    } else {
                        $fieldRules[] = 'max:10240'; // 10MB
                    }
                    break;
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
}

