<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Http\Requests\Api\ApiRequest;

class ProductImageStoreRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('ImageFileId')) {
            $this->merge([
                'file_id' => $this->input('ImageFileId'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'file_id' => ['required_without:ImageFileId', 'string', 'max:255'],
            'ImageFileId' => ['required_without:file_id', 'string', 'max:255'],
        ];
    }
}

