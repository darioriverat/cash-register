<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\StatusCodes;
use App\Models\Machine;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTransactionDetailsTest extends TestCase
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
    public function anUnauthenticatedUserCannotAccessToQueryTransactionDetails()
    {
        $response = $this->get(route('v1.transaction-details', 'POS-45'), ['Accept' => 'application/json']);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function itCanQueryTransactionDetails()
    {
        $machineId = Machine::select('id')->firstWhere('name', 'POS-45')->id;
        $transaction = Transaction::factory()->create([
            'machine_id' => $machineId,
            'total' => 150000
        ]);
        $transactionDetails = TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 50000,
            'quantity' => 3
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('v1.transaction-details', $transaction->id));

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL
            ],
            'cash' => [
                [
                    'exchange_type' => $transactionDetails->exchange_type,
                    'amount' => $transactionDetails->amount,
                    'quantity' => $transactionDetails->quantity
                ]
            ]
        ]);
    }
}
