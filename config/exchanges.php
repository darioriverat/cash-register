<?php

use App\Constants\ExchangeType;

return [
    'allowed' => [
        ExchangeType::COIN => [50, 100, 200, 500, 1000],
        ExchangeType::BILL => [1000, 5000, 10000, 20000, 50000, 100000]
    ]
];
