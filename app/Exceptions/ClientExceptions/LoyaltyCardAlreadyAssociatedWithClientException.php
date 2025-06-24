<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class LoyaltyCardAlreadyAssociatedWithClientException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified loyalty card is already associated with some client');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1011;
    }
}
