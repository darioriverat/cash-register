<?php

namespace App\Rules;

use App\Models\Balance;
use Illuminate\Contracts\Validation\Rule;

class AllowedExchangeRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $amount
     * @return bool
     */
    public function passes($attribute, $amount): bool
    {
        $type = $this->extractType($attribute);

        return Balance::where('exchange_type', $type)->where('amount', $amount)->count();
    }

    public function message(): string
    {
        return 'There is not exist an amount with the specified exchange type';
    }

    private function extractType(string $attribute)
    {
        return request()->input(str_replace('amount', 'exchange_type', $attribute));
    }
}
