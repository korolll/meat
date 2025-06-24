<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class AssortmentDataTypeCastImpossibleException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Can\'t cast data type');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1027;
    }
}
