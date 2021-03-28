<?php

namespace App\Http\Requests\Api;

use App\Constants\ExchangeType;
use App\Exceptions\ValidationApiException;
use App\Rules\AllowedExchangeRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'cash' => [
                'required',
                'array'
            ],
            'cash.*.exchange_type' => [
                'bail',
                'required',
                Rule::in(ExchangeType::supported())
            ],
            'cash.*.amount' => [
                'bail',
                'required',
                'numeric',
                new AllowedExchangeRule()
            ],
            'cash.*.quantity' => [
                'bail',
                'required',
                'numeric',
                'min:1'
            ]
        ];
    }

    /**
     * @param Validator $validator
     * @throws ValidationApiException
     */
    protected function failedValidation(Validator $validator)
    {
        throw ValidationApiException::fromValidator($validator);
    }
}
