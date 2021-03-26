<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\StatusCodes;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IncomeRequest;
use App\Models\Balance;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function initialBalance(IncomeRequest $request)
    {
        $cash = $request->input('cash');

        $transaction = Transaction::create(['type' => TransactionType::INCOME]);
        $transaction->details()->createMany($cash);

        foreach ($cash as $entry) {
            Balance::updateQuantity($entry);
        }

        return response()->rest(['status' => [
            'code' => StatusCodes::SUCCESSFUL,
            'description' => 'Initial balance created'
        ]]);
    }
}
