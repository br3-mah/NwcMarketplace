<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\ApiRequest;

class EmailResendRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $email = $this->input('Email', $this->input('email'));

        $this->merge([
            'email' => isset($email) ? (string) $email : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
        ];
    }
}
