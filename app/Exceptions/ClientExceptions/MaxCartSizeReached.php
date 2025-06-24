<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class MaxCartSizeReached extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Max cart size reached');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1028;
    }
}
