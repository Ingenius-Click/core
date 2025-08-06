<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ingenius\Core\Support\TenantInitializationManager;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantInitializationManager = app(TenantInitializationManager::class);
        $rules = $tenantInitializationManager->rules();

        $rules = array_merge($rules, [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
            'template' => 'required|string|exists:templates,identifier',
            'styles' => 'required|array',
        ]);

        return $rules;
    }
}
