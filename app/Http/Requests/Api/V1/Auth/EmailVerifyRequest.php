<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\ApiRequest;

class EmailVerifyRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $otp = $this->input('Otp', $this->input('otp'));
        $email = $this->input('Email', $this->input('email'));

        $this->merge([
            'otp' => isset($otp) ? (string) $otp : null,
            'email' => isset($email) ? (string) $email : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'otp' => ['required', 'string', 'regex:/^[0-9]{4,8}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
        ];
    }
}
