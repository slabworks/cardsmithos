<?php

namespace App\Http\Requests;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('card')) ?? false;
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
