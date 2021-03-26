<?php

namespace Tests\Feature\Api;

use App\Constants\ExchangeType;
use App\Constants\StatusCodes;
use App\Models\User;
use Database\Seeders\BalanceTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Concerns\HasIncomeRequestValues;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;
    use HasIncomeRequestValues;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(BalanceTableSeeder::class);
        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function anUnauthenticatedUserCannotAccessToCreateInitialBalance()
    {
        $balance = [
            [
                'exchange_type' => ExchangeType::BILL,
                'amount' => 20000,
                'quantity' => 5
            ],
        ];

        $response = $this->post(route('v1.initial-balance'), ['cash' => $balance], ['Accept' => 'application/json']);

        $response->assertStatus(401);
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

        $response = $this->actingAs($this->user)->post(route('v1.initial-balance'), ['cash' => $balance]);

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
        $response = $this->actingAs($this->user)->post(route('v1.initial-balance'), $payload);

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
