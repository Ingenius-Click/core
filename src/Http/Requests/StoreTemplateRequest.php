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
            'styles_vars' => 'nullable|array',
            'configurable' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:512',
        ];
    }
}
