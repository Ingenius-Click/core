<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ];
    }

}