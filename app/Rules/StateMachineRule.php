<?php

namespace App\Rules;

use App\Models\Machine;
use Illuminate\Contracts\Validation\Rule;

class StateMachineRule implements Rule
{
    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($machine = Machine::firstWhere('name', $value)) {
            return $machine->state === $this->state;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'This machine is not able for opening.';
    }
}
