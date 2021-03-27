<?php

namespace App\Models;

use App\Constants\MachineStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state'
    ];

    public static function openByName(string $name)
    {
        self::firstWhere('name', $name)->update(['state' => MachineStates::OPEN]);
    }
}
