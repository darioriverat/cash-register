<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\StatusCodes;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Helpers\CashHelper;
use App\Http\Requests\Api\V1\InitialBalanceRequest;
use App\Http\Requests\Api\V1\PaymentRequest;
use App\Http\Requests\Api\V1\QueryHistoricalBalanceRequest;
use App\Http\Requests\Api\V1\WithdrawRequest;
use App\Http\Requests\Api\V1\QueryTransactionsRequest;
use App\Models\Balance;
use App\Models\Machine;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function initialBalance(InitialBalanceRequest $request): JsonResponse
    {
        $machine = Machine::select('id')->firstWhere('name', $request->input('machine'));
        $cash = $request->input('cash');

        Transaction::createTransaction($machine->id, TransactionType::BASE, $cash);
        Balance::updateFromCash($machine->id, $cash);
        $machine->open();

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
            $balance = $machine->getCash();

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

            if ($changeCash) {
                Balance::discountChange($machine->id, $changeCash);
                Transaction::createTransaction($machine->id, TransactionType::OUTCOME, $changeCash);
            }
        }

        Balance::sumCash($machine->id, $cash);
        Transaction::createTransaction($machine->id, TransactionType::INCOME, $cash);

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
        $balance = $machine->getCash()->toArray();

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
            ],
            'cash' => $balance
        ], 200);
    }

    public function withdraw(Machine $machine): JsonResponse
    {
        $validator = Validator::make(['machine' => $machine->name], WithdrawRequest::rules());

        if ($validator->fails()) {
            return response()->rest([
                'status' => [
                    'code' => StatusCodes::VALIDATION_ERROR,
                    'description' => 'Client validation errors',
                    'error' => 'Impossible to perform this operation when machine state is not open'
                ],
            ], 400);
        }

        $balance = $machine->getCash();
        Transaction::createTransaction($machine->id, TransactionType::WITHDRAW, $balance->toArray());
        $machine->withdraw();

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
                'description' => 'successful withdraw'
            ]
        ], 200);
    }

    public function transactions(QueryTransactionsRequest $request, Machine $machine): JsonResponse
    {
        $transactions = Transaction::searchTransactions(
            $machine->id,
            $request->input('from', now()->subDay()),
            $request->input('to', now())
        );

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
            ],
            'transactions' => $transactions->toArray()
        ], 200);
    }

    public function transactionDetails(Transaction $transaction)
    {
        $entries = $transaction
            ->details()
            ->select('exchange_type', 'amount', 'quantity')
            ->get();

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
            ],
            'cash' => $entries->toArray()
        ], 200);
    }

    public function historicalBalance(QueryHistoricalBalanceRequest $request, Machine $machine): JsonResponse
    {
        $resume = Transaction::sumByType($machine->id, $request->input('to', now()->toISOString()));

        if (!$resume) {
            $resume = [
                'base' => 0,
                'income' => 0,
                'outcome' => 0,
                'withdraw' => 0
            ];
        } else {
            $resume = (array) array_shift($resume);
            array_walk($resume, function (&$item) {
                $item = (int) $item;
            });
        }

        return response()->rest([
            'status' => [
                'code' => StatusCodes::SUCCESSFUL,
            ],
            'resume' => [
                'transactions' => $resume,
                'balance' => $resume['base'] + $resume['income'] - $resume['outcome'] - $resume['withdraw']
            ]
        ], 200);
    }
}
