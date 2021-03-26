<?php

namespace Tests\Feature\Api;

use App\Constants\ExchangeType;
use App\Constants\StatusCodes;
use Database\Seeders\BalanceTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Concerns\HasIncomeRequestValues;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;
    use HasIncomeRequestValues;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(BalanceTableSeeder::class);
    }

    /**
     * @test
     */
    public function itCanSetInitialBalance()
    {
        $balance = [
            [
                'exchange_type' => ExchangeType::BILL,
                'amount' => 10000,
                'quantity' => 5
            ],
            [
                'exchange_type' => ExchangeType::BILL,
                'amount' => 20000,
                'quantity' => 2
            ],
            [
                'exchange_type' => ExchangeType::COIN,
                'amount' => 1000,
                'quantity' => 10
            ],
        ];

        $response = $this->post(route('initial-balance'), ['cash' => $balance]);

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
                'description' => 'Initial balance created'
            ]
        ]);
        foreach ($balance as $entry) {
            $this->assertDatabaseHas('balance', $entry);
        }
    }

    /**
     * @test
     * @dataProvider incomeValues
     * @param array $payload
     * @param string $error
     */
    public function itChecksValidationErrors(array $payload, string $error)
    {
        $response = $this->post(route('initial-balance'), $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::VALIDATION_ERROR,
                'description' => 'Client validation errors',
                'error' => $error
            ]
        ]);
    }
}
