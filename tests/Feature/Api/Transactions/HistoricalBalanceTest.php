<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\StatusCodes;
use App\Models\Machine;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Transactions\Concerns\HasInitialBalance;
use Tests\TestCase;

class HistoricalBalanceTest extends TestCase
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
        $response = $this->get(route('v1.historical-balance', 'POS-45'), ['Accept' => 'application/json']);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function itCanQueryFullHistoricalBalance()
    {
        $this->loadBalance();

        $machineId = Machine::select('id')->firstWhere('name', 'POS-45')->id;
        $transaction = Transaction::factory()->create([
            'machine_id' => $machineId,
            'total' => 150000
        ]);
        TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 50000,
            'quantity' => 3
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('v1.historical-balance', 'POS-45'));

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL
            ],
            'resume' => [
                'transactions' => [
                    'base' => 105000,
                    'income' => 150000,
                    'outcome' => 0,
                    'withdraw' => 0
                ],
                'balance' => 255000
            ]
        ]);
    }

    /**
     * @test
     */
    public function itCanQueryHistoricalBalanceInSpecificTime()
    {
        $this->loadBalance();

        $machineId = Machine::select('id')->firstWhere('name', 'POS-45')->id;
        $transaction = Transaction::factory()->create([
            'machine_id' => $machineId,
            'total' => 150000
        ]);
        TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 50000,
            'quantity' => 3
        ]);

        // this transactions is not counted
        $transaction = Transaction::factory()->create([
            'machine_id' => $machineId,
            'total' => 150000
        ]);
        $transaction->created_at = now()->addDay();
        $transaction->save();
        TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 50000,
            'quantity' => 400
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('v1.historical-balance', ['POS-45', 'to' => now()->toIso8601String()]));

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL
            ],
            'resume' => [
                'transactions' => [
                    'base' => 105000,
                    'income' => 150000,
                    'outcome' => 0,
                    'withdraw' => 0
                ],
                'balance' => 255000
            ]
        ]);
    }
}
