<?php

namespace App\Services;

use App\Models\File;
use App\Models\Record;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FormService
{
    /**
     * Handle file upload for form field
     */
    public function handleFileUpload(
        UploadedFile $file,
        FormField $formField,
        Record $record,
        User $user
    ): string {
        $tenantId = $user->tenant_id;

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'tenants/' . $tenantId . '/records/' . $record->id,
            $filename,
            'public'
        );

        // Create file record
        File::create([
            'tenant_id' => $tenantId,
            'record_id' => $record->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $formField->type,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'created_by' => $user->id,
        ]);

        return $path;
    }

    /**
     * Evaluate calculated field formula
     */
    public function evaluateFormula(string $formula, array $fieldValues): float
    {
        try {
            $expressionLanguage = new ExpressionLanguage();

            // Register common math functions
            $this->registerMathFunctions($expressionLanguage);

            // Replace field references {field_name} with expression language variables
            $expression = $formula;
            preg_match_all('/\{([^}]+)\}/', $formula, $matches);

            $variables = [];
            if (!empty($matches[1])) {
                foreach ($matches[1] as $fieldName) {
                    $value = $fieldValues[$fieldName] ?? 0;
                    $value = is_numeric($value) ? (float) $value : 0;
                    $variables[$fieldName] = $value;

                    $expression = str_replace('{' . $fieldName . '}', $fieldName, $expression);
                }
            }

            $result = $expressionLanguage->evaluate($expression, $variables);

            return is_numeric($result) ? round($result, 2) : 0;
        } catch (\Exception $e) {
            Log::error('Calculated field evaluation error', [
                'formula' => $formula,
                'error' => $e->getMessage(),
                'field_values' => $fieldValues,
            ]);

            return 0;
        }
    }

    /**
     * Register math functions for expression language
     */
    private function registerMathFunctions(ExpressionLanguage $expressionLanguage): void
    {
        $expressionLanguage->register('min', function (...$args) {
            return sprintf('min(%s)', implode(', ', $args));
        }, function ($arguments, ...$values) {
            return min(...$values);
        });

        $expressionLanguage->register('max', function (...$args) {
            return sprintf('max(%s)', implode(', ', $args));
        }, function ($arguments, ...$values) {
            return max(...$values);
        });

        $expressionLanguage->register('round', function ($value, $precision = 0) {
            return sprintf('round(%s, %s)', $value, $precision);
        }, function ($arguments, $value, $precision = 0) {
            return round($value, $precision);
        });

        $expressionLanguage->register('abs', function ($value) {
            return sprintf('abs(%s)', $value);
        }, function ($arguments, $value) {
            return abs($value);
        });

        $expressionLanguage->register('sqrt', function ($value) {
            return sprintf('sqrt(%s)', $value);
        }, function ($arguments, $value) {
            return sqrt($value);
        });

        $expressionLanguage->register('pow', function ($base, $exp) {
            return sprintf('pow(%s, %s)', $base, $exp);
        }, function ($arguments, $base, $exp) {
            return pow($base, $exp);
        });
    }
}

