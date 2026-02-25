<?php

namespace App\Http\Requests\Api\Form;

use App\Http\Requests\Api\BaseApiRequest;
use App\Models\Form;
use App\Models\FormField;

class UpdateFormDataRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formId = $this->route('form');
        
        if (!$formId) {
            return [];
        }

        $form = Form::with('formFields')->find($formId);
        
        if (!$form) {
            return [];
        }

        $rules = [];

        // Build validation rules from form fields
        foreach ($form->formFields as $field) {
            $fieldRules = [];

            $config = is_array($field->config_json) ? $field->config_json : (array) $field->config_json;
            $isRequired = $config['required'] ?? false;

            // For updates, fields are optional unless required
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

