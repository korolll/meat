<?php

namespace App\Services\Framework;

trait HasStaticMakeMethod
{
    /**
     * @param mixed $arguments
     * @return static
     */
    public static function make($arguments = null)
    {
        $arguments = is_array($arguments) ? $arguments : func_get_args();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return new static(...$arguments);
    }
}
