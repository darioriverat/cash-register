<?php

namespace App\Models;

use App\Constants\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'machine_id'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetails::class);
    }

    public static function createTransaction(int $machineId, string $type, array $cash): void
    {
        $transaction = Transaction::create(['type' => $type, 'machine_id' => $machineId]);
        $transaction->details()->createMany($cash);
    }
}
