<?php

namespace Database\Factories;

use App\Constants\TransactionType;
use App\Models\Machine;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type' => TransactionType::INCOME,
            'machine_id' => Machine::factory(),
            'total' => $this->faker->randomNumber(3)
        ];
    }
}
