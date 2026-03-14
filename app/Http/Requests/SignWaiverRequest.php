<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SignWaiverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_email' => ['required', 'email', 'max:255'],
            'agreed' => ['required', 'accepted'],
        ];
    }
}
