<?php

namespace App\Console\Commands;

use App\Constants\MachineStates;
use App\Models\Balance;
use App\Models\Machine;
use Illuminate\Console\Command;

class CreateMachineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:machine {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a cashier machine';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->createBalance($this->createMachine());
        $this->info('Cashier machine created');

        return self::SUCCESS;
    }

    private function createMachine(): Machine
    {
        return Machine::create([
            'name' => $this->argument('name'),
            'state' => MachineStates::CLOSED,
        ]);
    }

    private function createBalance(Machine $machine): void
    {
        foreach (config('exchanges.allowed') as $type => $amounts) {
            foreach ($amounts as $amount) {
                Balance::create([
                    'machine_id' => $machine->id,
                    'exchange_type' => $type,
                    'amount' => $amount,
                    'quantity' => 0
                ]);
            }
        }
    }
}
