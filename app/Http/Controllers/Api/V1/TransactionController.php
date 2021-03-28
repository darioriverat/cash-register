<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\StatusCodes;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Helpers\CashHelper;
use App\Http\Requests\Api\V1\InitialBalanceRequest;
use App\Http\Requests\Api\V1\PaymentRequest;
use App\Models\Balance;
use App\Models\Machine;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function initialBalance(InitialBalanceRequest $request): JsonResponse
    {
        $cash = $request->input('cash');

        Transaction::createTransaction(TransactionType::INCOME, $cash);

        foreach ($cash as $entry) {
            Balance::updateQuantity($entry);
        }

        Machine::openByName($request->input('machine'));

        return response()->rest(['status' => [
            'code' => StatusCodes::SUCCESSFUL,
            'description' => 'Initial balance created'
        ]]);
    }

    public function payment(PaymentRequest $request): JsonResponse
    {
        $cash = $request->input('cash');
        $totalPayment = $request->input('payment')['amount'];

        $totalCash = CashHelper::sum($cash);

        if ($totalCash < $totalPayment) {
            return response()->rest([
                'status' => [
                    'code' => StatusCodes::TRANSACTION_ERROR,
                    'description' => 'insufficient funds to pay the total amount'
                ]
            ], 422);
        }

        $changeCash = [];
        $change = $totalCash - $totalPayment;

        /** @var Machine $machine */
        $machine = Machine::firstWhere('name', $request->input('machine'));

        if ($change) {
            $balance = $machine->getCleanBalance();

            foreach ($balance as $entry) {
                if ($entry->amount < $change) {
                    $needed = (int) floor($change / $entry->amount);

                    $item = [
                        'exchange_type' => $entry->exchange_type,
                        'amount' => $entry->amount,
                        'quantity' => $entry->quantity >= $needed ? $needed : $entry->quantity,
                    ];

                    $change -= $item['quantity'] * $item['amount'];
                    $changeCash[] = $item;
                }
            }

            if ($change) {
                return response()->rest([
                    'status' => [
                        'code' => StatusCodes::TRANSACTION_ERROR,
                        'description' => 'impossible to give change for ' . $change
                    ]
                ], 422);
            }

            foreach ($changeCash as $entry) {
                Balance::subQuantity($machine->id, $entry);
            }
        }

        foreach ($cash as $entry) {
            Balance::sumQuantity($machine->id, $entry);
        }

        Transaction::createTransaction(TransactionType::INCOME, $cash);

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
                'description' => 'payment approved'
            ],
            'change' => $changeCash
        ], 201);
    }

    public function balance(Machine $machine): JsonResponse
    {
        $balance = $machine->getCleanBalance()->toArray();

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
            ],
            'cash' => $balance
        ], 200);
    }
}
