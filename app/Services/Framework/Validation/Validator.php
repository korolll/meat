<?php

namespace App\Services\Framework\Validation;

use Illuminate\Support\Str;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    /**
     * @param string $attribute
     * @return string
     */
    public function getDisplayableAttribute($attribute)
    {
        $string = parent::getDisplayableAttribute($attribute);

        return Str::snake($string);
    }
}
