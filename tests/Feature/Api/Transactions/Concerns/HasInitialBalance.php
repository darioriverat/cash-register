<?php

namespace Tests\Feature\Api\Transactions\Concerns;

use App\Constants\ExchangeType;
use App\Constants\MachineStates;
use App\Constants\TransactionType;
use App\Models\Balance;
use App\Models\Machine;
use App\Models\Transaction;
use App\Models\TransactionDetails;

trait HasInitialBalance
{
    protected function loadBalance(): void
    {
        $machine = Machine::select('id')->firstWhere('name', 'POS-45');
        $machine->state = MachineStates::OPEN;
        $machine->save();
        $machineId = $machine->id;

        $transaction = Transaction::factory()->create([
            'machine_id' => $machineId,
            'type' => TransactionType::BASE,
            'total' => 105000
        ]);
        TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 20000,
            'quantity' => 4
        ]);
        TransactionDetails::factory()->create([
            'transaction_id' => $transaction->id,
            'exchange_type' => ExchangeType::BILL,
            'amount' => 5000,
            'quantity' => 5
        ]);

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
