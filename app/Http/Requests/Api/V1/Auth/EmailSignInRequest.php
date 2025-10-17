<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class EmailSignInRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $fname = $this->input('Fname', $this->input('fname'));
        $lname = $this->input('Lname', $this->input('lname'));
        $gender = $this->input('Gender', $this->input('gender'));
        $email = $this->input('Email', $this->input('email'));

        $this->merge([
            'fname' => isset($fname) ? (string) $fname : null,
            'lname' => isset($lname) ? (string) $lname : null,
            'gender' => isset($gender) ? strtoupper((string) $gender) : null,
            'email' => isset($email) ? (string) $email : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'fname' => ['required', 'string', 'max:191'],
            'lname' => ['required', 'string', 'max:191'],
            'gender' => [
                'nullable',
                'string',
                'max:20',
                Rule::in(['M', 'F', 'O', 'MALE', 'FEMALE', 'OTHER', 'X', 'NON-BINARY', 'NONBINARY']),
            ],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
        ];
    }
}
