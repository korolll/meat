<?php

namespace App\Exceptions;

use Exception;

class TealsyException extends Exception
{
    /**
     * @param \Throwable $e
     * @return static
     */
    public static function wrap(\Throwable $e)
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }
}
