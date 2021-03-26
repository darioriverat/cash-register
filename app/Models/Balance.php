<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $table = 'balance';

    protected $fillable = [
        'exchange_type',
        'amount',
        'quantity'
    ];

    /**
     * @param array $entry
     * @return mixed
     */
    public static function updateQuantity(array $entry)
    {
        return Balance::where('exchange_type', $entry['exchange_type'])
            ->where('amount', $entry['amount'])
            ->update([
                'quantity' => $entry['quantity']
            ]);
    }
}
