<?php

namespace Tests\Feature\Api\Concerns;

use App\Constants\ExchangeType;

trait HasIncomeRequestValues
{
    public function incomeValues(): array
    {
        return [
            'Missing machine' => [
                [
                    'cash' => [
                        'exchange_type' => ExchangeType::BILL,
                        'amount' => 10000,
                        'quantity' => 5
                    ]
                ],
                'The machine field is required.'
            ],
            'Machine not in database' => [
                [
                    'machine' => 'POS-89965',
                    'cash' => [
                        'exchange_type' => ExchangeType::BILL,
                        'amount' => 10000,
                        'quantity' => 5
                    ]
                ],
                'The selected machine is invalid.'
            ],
            'Missing cash' => [
                [
                    'machine' => 'POS-45'
                ],
                'The cash field is required.'
            ],
            'Missing exchange type' => [
                $this->cash([
                    [
                        'amount' => 10000,
                        'quantity' => 5
                    ]
                ]),
                'The cash.0.exchange_type field is required.'
            ],
            'Missing amount' => [
                $this->cash([
                    [
                        'exchange_type' => ExchangeType::COIN,
                        'quantity' => 5
                    ]
                ]),
                'The cash.0.amount field is required.'
            ],
            'Missing quantity' => [
                $this->cash([
                    [
                        'exchange_type' => ExchangeType::BILL,
                        'amount' => 10000,
                    ]
                ]),
                'The cash.0.quantity field is required.'
            ],
            'Wrong exchange type' => [
                $this->cash([
                    [
                        'exchange_type' => 'invalid exchange type',
                        'amount' => 10000,
                        'quantity' => 5
                    ]
                ]),
                'The selected cash.0.exchange_type is invalid.'
            ],
            'Wrong amount' => [
                $this->cash([
                    [
                        'exchange_type' => ExchangeType::BILL,
                        'amount' => -1,
                        'quantity' => 5
                    ]
                ]),
                'There is not exist an amount with the specified exchange type'
            ],
            'Wrong quantity' => [
                $this->cash([
                    [
                        'exchange_type' => ExchangeType::BILL,
                        'amount' => 10000,
                        'quantity' => -1
                    ]
                ]),
                'The cash.0.quantity must be at least 1.'
            ],
        ];
    }

    private function cash(array $payload): array
    {
        return [
            'machine' => 'POS-45',
            'cash' => $payload
        ];
    }
}
