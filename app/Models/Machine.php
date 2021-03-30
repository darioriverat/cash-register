<?php

namespace App\Models;

use App\Constants\MachineStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property int machine_id
 * @property string name
 */
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

    public function getCash(): Collection
    {
        return $this
            ->balance()
            ->select('exchange_type', 'amount', 'quantity')
            ->where('quantity', '>', 0)
            ->orderBy('amount', 'DESC')
            ->get();
    }

    public function withdraw(): void
    {
        $this->balance()->update(['quantity' => 0]);
        $this->attributes['state'] = MachineStates::CLOSED;
        $this->save();
    }

    public function open(): self
    {
        $this->attributes['state'] = MachineStates::OPEN;
        $this->save();

        return $this;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }
}
