<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Transactions\Concerns\HasInitialBalance;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    use RefreshDatabase;
    use HasInitialBalance;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('make:machine', ['name' => 'POS-45']);
        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function anUnauthenticatedUserCannotAccessToCreateInitialBalance()
    {
        $response = $this->get(route('v1.balance', 'POS-45'), ['Accept' => 'application/json']);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function itCanQueryTheMachineCashierBalance()
    {
        $this->loadBalance();

        $response = $this->actingAs($this->user)->get(route('v1.balance', 'POS-45'), ['Accept' => 'application/json']);

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => 1000,
            ],
            'cash' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 20000,
                    'quantity' => 4
                ],
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 5000,
                    'quantity' => 5
                ],
            ]
        ]);
    }
}
