<?php

namespace Database\Seeders;

use App\Models\Balance;
use Illuminate\Database\Seeder;

class BalanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (config('exchanges.allowed') as $type => $amounts) {
            foreach ($amounts as $amount) {
                Balance::create([
                    'exchange_type' => $type,
                    'amount' => $amount,
                    'quantity' => 0
                ]);
            }
        }
    }
}
