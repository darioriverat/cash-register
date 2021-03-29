<?php

namespace App\Http\Requests\Api\V1;

use App\Constants\MachineStates;
use App\Rules\StateMachineRule;

class WithdrawRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'machine' => [
                'bail',
                'required',
                'exists:machines,name',
                new StateMachineRule(MachineStates::OPEN)
            ]
        ];
    }
}
