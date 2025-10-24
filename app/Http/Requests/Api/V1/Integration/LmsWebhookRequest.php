<?php

namespace App\Http\Requests\Api\V1\Integration;

use App\Http\Requests\Api\ApiRequest;

class LmsWebhookRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'event_type' => ['nullable', 'string', 'max:191'],
        ];
    }
}

