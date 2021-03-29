<?php

namespace Database\Factories;

use App\Constants\ExchangeType;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionDetailsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransactionDetails::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 5
        ];
    }
}
