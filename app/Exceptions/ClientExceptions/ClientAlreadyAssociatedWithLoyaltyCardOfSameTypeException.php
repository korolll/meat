<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class ClientAlreadyAssociatedWithLoyaltyCardOfSameTypeException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified client is already associated with loyalty card of same type');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1006;
    }
}
