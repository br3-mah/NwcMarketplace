<?php

namespace App\Http\Requests\Api\V1\User;

use App\Http\Requests\Api\ApiRequest;

class UserIndexRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'max:50'],
            'q' => ['sometimes', 'string', 'max:191'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

