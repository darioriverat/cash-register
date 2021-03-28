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
     * @param array $entry
     * @return mixed
     */
    public static function updateQuantity(array $entry)
    {
        return self::where('exchange_type', $entry['exchange_type'])
            ->where('amount', $entry['amount'])
            ->update([
                'quantity' => $entry['quantity']
            ]);
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

    public static function checkFundsByMachineId(int $machineId): int
    {
        return self::select(DB::raw('SUM(amount * quantity) as total'))
            ->where('machine_id', $machineId)
            ->first()
            ->total;
    }
}
