<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class LoyaltyCardNotAssociatedWithClientException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified loyalty card is not associated with some client');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1012;
    }
}
