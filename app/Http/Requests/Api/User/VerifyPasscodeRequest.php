<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\BaseApiRequest;

class VerifyPasscodeRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passcode' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'passcode.required' => 'Passcode is required',
            'passcode.size' => 'Passcode must be exactly 6 digits',
            'passcode.regex' => 'Passcode must contain only numbers',
        ];
    }
}

