<?php

namespace App\Http\Requests\Api\V1\Notification;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class NotificationTemplateUpdateRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->input('code'),
            'name' => $this->input('name'),
            'channel' => strtolower((string) $this->input('channel', 'in_app')),
            'subject' => $this->input('subject'),
            'body' => $this->input('body'),
            'metadata' => $this->input('metadata', []),
        ]);
    }

    public function rules(): array
    {
        $templateId = $this->route('template')->id ?? $this->route('templateId');

        return [
            'code' => [
                'required',
                'string',
                'max:191',
                Rule::unique('notification_templates', 'code')->ignore($templateId),
            ],
            'name' => ['required', 'string', 'max:191'],
            'channel' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:191'],
            'body' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

