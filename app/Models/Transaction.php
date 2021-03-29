<?php

namespace App\Models;

use App\Http\Requests\Api\Helpers\CashHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'machine_id',
        'total'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetails::class);
    }

    public function scopeFrom(Builder $query, string $term = null, $boolean = 'and'): Builder
    {
        $date = Carbon::parse($term);

        return $query->whereDate('created_at', '>=', $date->toDateString(), $boolean);
    }

    public function scopeTo(Builder $query, string $term = null, $boolean = 'and'): Builder
    {
        $date = Carbon::parse($term);

        return $query->whereDate('created_at', '<=', $date->toDateString(), $boolean);
    }

    public static function createTransaction(int $machineId, string $type, array $cash): void
    {
        $total = CashHelper::sum($cash);
        $transaction = Transaction::create([
            'type' => $type,
            'machine_id' => $machineId,
            'total' => $total
        ]);
        $transaction->details()->createMany($cash);
    }

    public static function searchTransactions(int $machineId, string $from, string $to): Collection
    {
        return self::select('id', 'type', 'total', 'created_at')
            ->where('machine_id', $machineId)
            ->from($from)
            ->to($to)
            ->get();
    }

    public static function sumByType(int $machineId, string $to)
    {
        $sql = "select total as base, (
                    select sum(total) from transactions as inc
                    where inc.machine_id = tx.machine_id and type = 'income'
                    and created_at <= '$to'
                ) as income, (
                    select sum(total) from transactions as inc
                    where inc.machine_id = tx.machine_id and type = 'outcome'
                    and created_at <= '$to'
                ) as outcome, (
                    select sum(total) from transactions as inc
                    where inc.machine_id = tx.machine_id and type = 'withdraw'
                    and created_at <= '$to'
                ) as withdraw
                from transactions tx
                where machine_id = $machineId and type = 'base'
                and created_at <= '$to';";

        return DB::select($sql);
    }
}
