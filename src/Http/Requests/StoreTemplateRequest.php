<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'identifier' => 'required|string|max:255|unique:templates,identifier',
            'features' => 'required|array',
            'features.*' => 'string',
            'styles' => 'nullable|string',
            'configurable' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'new_images' => 'nullable|array',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:512',
        ];
    }
}
