<?php

namespace App\Http\Requests;

use App\Enums\ConversationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ConversationStatus::class)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('user_id', $this->user()->id)],
            'subject' => ['nullable', 'string', 'max:255'],
        ];
    }
}
