<?php

namespace App\Http\Requests;

use App\Models\BusinessStatistic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        $businessStatistic = $this->route('businessStatistic');

        return $businessStatistic instanceof BusinessStatistic
            ? $this->user()?->can('update', $businessStatistic) ?? false
            : false;
    }

    public function rules(): array
    {
        return (new StoreBusinessStatisticRequest)->rules();
    }
}
