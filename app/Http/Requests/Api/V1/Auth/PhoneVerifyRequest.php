<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\ApiRequest;

class PhoneVerifyRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $otp = $this->input('Otp', $this->input('otp'));
        $phone = $this->input('Phone', $this->input('phone'));

        $this->merge([
            'otp' => isset($otp) ? (string) $otp : null,
            'phone' => isset($phone) ? (string) $phone : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'otp' => ['required', 'string', 'regex:/^[0-9]{4,8}$/'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{9,15}$/'],
        ];
    }
}
