<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class LoyaltyCardNotFoundException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified loyalty card is not found');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1013;
    }
}
