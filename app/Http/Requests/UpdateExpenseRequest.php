<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('expense')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'category' => ['nullable', Rule::enum(ExpenseCategory::class)],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
