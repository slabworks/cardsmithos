<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('customer')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'card_ids' => ['required', 'array', 'min:1'],
            'card_ids.*' => [
                'integer',
                Rule::exists('cards', 'id')->where('customer_id', $customer->id),
            ],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'packaging' => ['nullable', 'numeric', 'min:0'],
            'handling' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
