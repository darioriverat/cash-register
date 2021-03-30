<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Balance extends Model
{
    use HasFactory;

    protected $table = 'balance';

    protected $fillable = [
        'machine_id',
        'exchange_type',
        'amount',
        'quantity'
    ];

    /**
     * @param int $machineId
     * @param array $entry
     * @return mixed
     */
    public static function updateQuantity(int $machineId, array $entry)
    {
        return self::where('machine_id', $machineId)
            ->where('exchange_type', $entry['exchange_type'])
            ->where('amount', $entry['amount'])
            ->update([
                'quantity' => $entry['quantity']
            ]);
    }

    public static function updateFromCash(int $machineId, array $cash): void
    {
        foreach ($cash as $entry) {
            self::updateQuantity($machineId, $entry);
        }
    }

    /**
     * @param int $machineId
     * @param array $entry
     * @return mixed
     */
    public static function sumQuantity(int $machineId, array $entry)
    {
        return self::where('machine_id', $machineId)
            ->where('exchange_type', $entry['exchange_type'])
            ->where('amount', $entry['amount'])
            ->update([
                'quantity' => DB::raw('quantity + ' . $entry['quantity'])
            ]);
    }

    public static function sumCash(int $machineId, array $cash): void
    {
        foreach ($cash as $entry) {
            Balance::sumQuantity($machineId, $entry);
        }
    }

    /**
     * @param int $machineId
     * @param array $entry
     * @return mixed
     */
    public static function subQuantity(int $machineId, array $entry)
    {
        return self::where('machine_id', $machineId)
            ->where('exchange_type', $entry['exchange_type'])
            ->where('amount', $entry['amount'])
            ->update([
                'quantity' => DB::raw('quantity - ' . $entry['quantity'])
            ]);
    }

    public static function discountChange(int $machineId, array $cash): void
    {
        foreach ($cash as $entry) {
            Balance::subQuantity($machineId, $entry);
        }
    }
}
