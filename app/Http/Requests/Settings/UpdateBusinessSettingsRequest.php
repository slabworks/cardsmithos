<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'store_slug' => [
                'nullable',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('business_settings', 'store_slug')
                    ->ignore($this->user()->businessSettings?->id),
                Rule::notIn(['admin', 'api', 'settings', 'dashboard', 'login', 'register']),
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'instagram_handle' => ['nullable', 'string', 'max:30', 'regex:/^[a-zA-Z0-9._]+$/'],
            'tiktok_handle' => ['nullable', 'string', 'max:24', 'regex:/^[a-zA-Z0-9._]+$/'],
            'country' => ['nullable', 'string', 'size:2'],
            'location_name' => ['nullable', 'string', 'max:100'],
            'is_listed_in_directory' => ['boolean'],
        ];
    }
}
