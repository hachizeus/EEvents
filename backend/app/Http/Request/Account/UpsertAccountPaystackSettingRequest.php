<?php

namespace HiEvents\Http\Request\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAccountPaystackSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'public_key' => ['required', 'string', 'starts_with:pk_'],
            'secret_key' => ['required', 'string', 'starts_with:sk_'],
        ];
    }

    public function messages(): array
    {
        return [
            'public_key.starts_with' => __('The public key must start with pk_test_ or pk_live_.'),
            'secret_key.starts_with' => __('The secret key must start with sk_test_ or sk_live_.'),
        ];
    }
}
