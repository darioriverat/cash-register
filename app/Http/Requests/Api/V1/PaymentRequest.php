<?php

namespace App\Http\Requests\Api\V1;

use App\Constants\MachineStates;
use App\Http\Requests\Api\CashRequest;
use App\Rules\StateMachineRule;

class PaymentRequest extends CashRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'machine' => [
                'bail',
                'required',
                'exists:machines,name',
                new StateMachineRule(MachineStates::OPEN)
            ],
            'payment' => [
                'bail',
                'required',
                'array'
            ],
            'payment.amount' => [
                'bail',
                'required',
                'numeric',
                'min:50'
            ]
        ]);
    }
}
