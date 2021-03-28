<?php

namespace App\Models;

use App\Constants\MachineStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state'
    ];

    public function balance(): HasMany
    {
        return $this->hasMany(Balance::class);
    }

    public function getCleanBalance(): Collection
    {
        return $this
            ->balance()
            ->where('quantity', '>', 0)
            ->orderBy('amount', 'DESC')
            ->get();
    }

    public static function openByName(string $name)
    {
        self::firstWhere('name', $name)->update(['state' => MachineStates::OPEN]);
    }
}
