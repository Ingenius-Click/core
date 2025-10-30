<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'new_images' => 'nullable|array',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:512',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'nullable|integer',
            'styles' => 'nullable|string',
            'configurable' => 'nullable|boolean',
            'features' => 'sometimes|array',
            'features.*' => 'string',
        ];
    }
}
