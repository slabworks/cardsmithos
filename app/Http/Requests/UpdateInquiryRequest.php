<?php

namespace App\Http\Requests;

use App\Enums\CommunicationMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('inquiry')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_username' => ['required', 'string', 'max:255'],
            'communication_method' => ['nullable', Rule::enum(CommunicationMethod::class)],
            'inquired_at' => ['required', 'date'],
            'converted' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}
