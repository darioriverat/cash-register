<?php

namespace App\Http\Requests\Api\V1;

use App\Constants\ExchangeType;
use App\Exceptions\CustomException;
use App\Rules\AllowedExchangeRule;
use App\Rules\StateMachineRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeRequest extends FormRequest
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
            'machine' => [
                'bail',
                'required',
                'exists:machines,name',
                new StateMachineRule()
            ],
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
     * @throws CustomException
     */
    protected function failedValidation(Validator $validator)
    {
        throw CustomException::fromValidator($validator);
    }
}
