<?php

namespace App\Http\Requests;

use App\Enums\SubmissionReferralSource;
use App\Enums\SubmissionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $submission !== null && $this->user()?->can('update', $submission) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('user_id', $this->user()?->id),
            ],
            'status' => ['required', Rule::enum(SubmissionStatus::class)],
            'notes' => ['nullable', 'string'],
            'referral_source' => ['nullable', Rule::enum(SubmissionReferralSource::class)],
        ];
    }
}
