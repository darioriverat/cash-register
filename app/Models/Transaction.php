<?php

namespace App\Models;

use App\Http\Requests\Api\Helpers\CashHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function scopeCreatedAt(Builder $query, string $term = null, $boolean = 'and'): Builder
    {
        $date = Carbon::parse($term);

        return $query->whereDate('created_at', '<=', $date->toDateString(), $boolean);
    }
}
