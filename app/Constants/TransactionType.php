<?php

namespace App\Constants;

use App\Concerns\HasEnumValues;

class TransactionType
{
    use HasEnumValues;

    public const BASE = 'base';
    public const INCOME = 'income';
    public const OUTCOME = 'outcome';
    public const WITHDRAW = 'withdraw';
}
