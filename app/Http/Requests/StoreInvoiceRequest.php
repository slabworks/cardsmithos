<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('submission')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $submission = $this->route('submission');

        return [
            'card_ids' => ['required', 'array', 'min:1'],
            'card_ids.*' => [
                'integer',
                Rule::exists('cards', 'id')->where('submission_id', $submission->id),
            ],
            'line_item_prices' => ['nullable', 'array'],
            'line_item_prices.*' => ['numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'packaging' => ['nullable', 'numeric', 'min:0'],
            'handling' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
