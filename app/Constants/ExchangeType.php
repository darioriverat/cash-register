<?php

namespace App\Constants;

use App\Concerns\HasEnumValues;

class ExchangeType
{
    use HasEnumValues;

    public const BILL = 'bill';
    public const COIN = 'coin';
}
