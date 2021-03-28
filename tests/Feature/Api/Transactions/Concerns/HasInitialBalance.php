<?php

namespace Tests\Feature\Api\Transactions\Concerns;

use App\Constants\ExchangeType;
use App\Constants\MachineStates;
use App\Models\Balance;
use App\Models\Machine;

trait HasInitialBalance
{
    protected function loadBalance(): void
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
