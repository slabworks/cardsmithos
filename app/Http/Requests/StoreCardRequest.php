<?php

namespace App\Http\Requests;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $this->user()?->can('update', $customer) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'work_done' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(CardStatus::class)],
            'condition_before' => ['nullable', Rule::enum(CardCondition::class)],
            'condition_after' => ['nullable', Rule::enum(CardCondition::class)],
            'restoration_hours' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ];
    }
}
