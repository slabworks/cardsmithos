<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'default_fixed_rate' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
