<?php

namespace App\Http\Requests\Api\Helpers;

class CashHelper
{
    public static function sum(array $cash)
    {
        return array_sum(array_map(function ($entry) {
            return $entry['amount'] * $entry['quantity'];
        }, $cash));
    }
}
