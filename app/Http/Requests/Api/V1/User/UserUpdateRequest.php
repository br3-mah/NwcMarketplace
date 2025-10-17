<?php

namespace App\Http\Requests\Api\V1\User;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('gender') && $this->filled('gender')) {
            $this->merge([
                'gender' => strtoupper((string) $this->input('gender')),
            ]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'fname' => ['sometimes', 'nullable', 'string', 'max:191'],
            'lname' => ['sometimes', 'nullable', 'string', 'max:191'],
            'gender' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::in(['M', 'F', 'O', 'MALE', 'FEMALE', 'OTHER', 'X', 'NON-BINARY', 'NONBINARY']),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:32',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
        ];
    }
}
