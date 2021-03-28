<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\MachineStates;
use App\Constants\StatusCodes;
use App\Constants\TransactionType;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Transactions\Concerns\HasIncomeRequestValues;
use Tests\TestCase;

class InitialBalanceTest extends TestCase
{
    use RefreshDatabase;
    use HasIncomeRequestValues;

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
    public function itChecksWhenMachineIsAbleForOpening()
    {
        $machine = Machine::firstWhere('name', 'POS-45');
        $machine->state = MachineStates::OPEN;
        $machine->save();

        $payload = [
            'machine' => 'POS-45',
            'cash' => [
                'exchange_type' => ExchangeType::BILL,
                'amount' => 10000,
                'quantity' => 5
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('v1.initial-balance'), $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::VALIDATION_ERROR,
                'description' => 'Client validation errors',
                'error' => 'Impossible to perform this operation when machine state is not closed'
            ]
        ]);
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
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('v1.initial-balance'), [
            'machine' => 'POS-45',
            'cash' => $balance
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
                'description' => 'Initial balance created'
            ]
        ]);
        $this->assertDatabaseHas('machines', [
            'name' => 'POS-45',
            'state' => MachineStates::OPEN
        ]);
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionType::BASE
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
