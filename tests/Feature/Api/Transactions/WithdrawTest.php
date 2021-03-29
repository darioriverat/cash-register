<?php

namespace Tests\Feature\Api\Transactions;

use App\Constants\ExchangeType;
use App\Constants\MachineStates;
use App\Constants\StatusCodes;
use App\Constants\TransactionType;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Transactions\Concerns\HasInitialBalance;
use Tests\TestCase;

class WithdrawTest extends TestCase
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
    public function anUnauthenticatedUserCannotAccessToPerformWithdraw()
    {
        $response = $this->post(route('v1.payment', 'POS-45'), [], ['Accept' => 'application/json']);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function itChecksWhenMachineIsAbleForOpening()
    {
        $this->loadBalance();
        $machine = Machine::firstWhere('name', 'POS-45');
        $machine->state = MachineStates::CLOSED;
        $machine->save();

        $response = $this->actingAs($this->user)->post(route('v1.withdraw', $machine));

        $response->assertStatus(400);
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::VALIDATION_ERROR,
                'description' => 'Client validation errors',
                'error' => 'Impossible to perform this operation when machine state is not open'
            ]
        ]);
    }

    /**
     * @test
     */
    public function itCanPerformWithdraws()
    {
        $machineId = Machine::select('id')->firstWhere('name', 'POS-45')->id;
        $this->loadBalance();

        $response = $this
            ->actingAs($this->user)
            ->post(route('v1.withdraw', 'POS-45'), [], ['Accept' => 'application/json']);

        $response->assertOk();
        $response->assertJson([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
                'description' => 'successful withdraw'
            ]
        ]);
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionType::OUTCOME,
            'machine_id' => $machineId
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 4
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'exchange_type' => ExchangeType::BILL,
            'amount' => 5000,
            'quantity' => 5
        ]);
        $this->assertDatabaseHas('balance', [
            'machine_id' => $machineId,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 0
        ]);
        $this->assertDatabaseHas('balance', [
            'machine_id' => $machineId,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 5000,
            'quantity' => 0
        ]);
    }
}
