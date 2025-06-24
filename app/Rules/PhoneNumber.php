<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $conditions = [];

        $conditions[] = strpos($value, "+") === 0;
        $conditions[] = strlen($value) === 12;
        $conditions[] = preg_match("/[^\d+]/i", $value) === 0;

        return (bool)array_product($conditions);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.regex');
    }
}
