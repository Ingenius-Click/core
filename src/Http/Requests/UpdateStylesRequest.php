<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStylesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'styles' => 'required|array',
        ];
    }
}
