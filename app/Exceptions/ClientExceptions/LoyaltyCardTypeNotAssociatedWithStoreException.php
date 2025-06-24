<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class LoyaltyCardTypeNotAssociatedWithStoreException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified loyalty card type is not associated with store');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1014;
    }
}
