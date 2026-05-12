<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Submission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission') instanceof Submission ? $this->route('submission') : null;

        return $submission !== null && $this->user()?->can('update', $submission) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'method' => $this->filled('method') ? $this->input('method') : null,
            'reference' => $this->filled('reference') ? $this->input('reference') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'paid_at' => ['required', 'date'],
            'method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
