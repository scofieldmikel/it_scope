<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidQuantity implements Rule
{
    /**
     * @var null
     */


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return is_numeric($value) && $value > 0 && $value === (int)$value;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute must be a positive integer.';
    }
}
