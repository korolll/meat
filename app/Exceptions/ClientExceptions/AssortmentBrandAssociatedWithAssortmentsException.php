<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class AssortmentBrandAssociatedWithAssortmentsException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Assortment brand is associated with some assortments');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1001;
    }
}
