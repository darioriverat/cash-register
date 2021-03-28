<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\MachineStates;
use App\Models\Balance;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

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
    public function anUnauthenticatedUserCannotAccessToCreatePayments()
    {
        $payload = [
            'machine' => 'POS-45',
            'cash' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 20000,
                    'quantity' => 2
                ],
            ],
            'payment' => [
                'amount' => 25000
            ]
        ];

        $response = $this->post(route('v1.payment'), $payload, ['Accept' => 'application/json']);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function itCanReceivePayments()
    {
        $this->loadBalance();
        $payload = [
            'machine' => 'POS-45',
            'cash' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 20000,
                    'quantity' => 2
                ]
            ],
            'payment' => [
                'amount' => 25000
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload, ['Accept' => 'application/json']);

        $response->assertCreated();
        $response->assertJson([
            'status' => [
                'code' => 1000,
                'description' => 'payment approved'
            ],
            'change' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 5000,
                    'quantity' => 3
                ]
            ]
        ]);
        $this->assertDatabaseHas('balance', [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 6
        ]);
        $this->assertDatabaseHas('balance', [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 5000,
            'quantity' => 2
        ]);
    }

    /**
     * @test
     */
    public function itCannotPerformPaymentsWhenCashIsNotEnough()
    {
        $this->loadBalance();
        $payload = [
            'machine' => 'POS-45',
            'cash' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 20000,
                    'quantity' => 2
                ]
            ],
            'payment' => [
                'amount' => 55000
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => [
                'code' => 1200,
                'description' => 'insufficient funds to pay the total amount'
            ]
        ]);
    }

    /**
     * @test
     */
    public function itCannotPerformPaymentsWhenIsImpossibleToGiveChange()
    {
        $this->loadBalance();
        $payload = [
            'machine' => 'POS-45',
            'cash' => [
                [
                    'exchange_type' => ExchangeType::BILL,
                    'amount' => 20000,
                    'quantity' => 2
                ]
            ],
            'payment' => [
                'amount' => 25500
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => [
                'code' => 1200,
                'description' => 'impossible to give change for 4500'
            ]
        ]);
    }

    private function loadBalance(): void
    {
        $machine = Machine::select('id')->firstWhere('name', 'POS-45');
        $machine->state = MachineStates::OPEN;
        $machine->save();
        $machineId = $machine->id;

        Balance::sumQuantity($machineId, [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 4
        ]);

        Balance::sumQuantity($machineId, [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 5000,
            'quantity' => 5
        ]);
    }
}
