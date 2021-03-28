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
        'type'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetails::class);
    }

    public static function createTransaction(string $type, array $cash): void
    {
        $transaction = Transaction::create(['type' => $type]);
        $transaction->details()->createMany($cash);
    }
}
