<?php

namespace App\Http\Requests;

use App\Enums\SubmissionReferralSource;
use App\Enums\SubmissionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubmissionRequest extends FormRequest
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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'name' => ['required_without:customer_id', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(SubmissionStatus::class)],
            'notes' => ['nullable', 'string'],
            'referral_source' => ['nullable', Rule::enum(SubmissionReferralSource::class)],
        ];
    }
}
