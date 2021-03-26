<?php

namespace App\Concerns;

trait HasEnumValues
{
    public static function supported(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
