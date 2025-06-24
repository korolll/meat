<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class AssortmentPropertyDataTypeNotEnumException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Assortment property data type not `enum`');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1026;
    }
}
