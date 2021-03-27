<?php

namespace App\Constants;

use App\Concerns\HasEnumValues;

class MachineStates
{
    use HasEnumValues;

    public const OPEN = 'open';
    public const CLOSED = 'closed';
}
