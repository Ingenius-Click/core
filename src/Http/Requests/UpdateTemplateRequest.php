<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:512',
            'styles_vars' => 'nullable|array',
        ];
    }
}
