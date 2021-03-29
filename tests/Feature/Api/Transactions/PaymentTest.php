<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\TransactionType;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Transactions\Concerns\HasInitialBalance;
use Tests\TestCase;

class PaymentTest extends TestCase
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
        $machineId = Machine::select('id')->firstWhere('name', 'POS-45')->id;
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

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload);

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
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionType::INCOME,
            'machine_id' => $machineId,
            'total' => 40000
        ]);
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionType::OUTCOME,
            'machine_id' => $machineId,
            'total' => 15000
        ]);
        foreach ($payload['cash'] as $entry) {
            $this->assertDatabaseHas('transaction_details', $entry);
        }
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

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload);

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

        $response = $this->actingAs($this->user)->post(route('v1.payment'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => [
                'code' => 1200,
                'description' => 'impossible to give change for 4500'
            ]
        ]);
    }
}
