<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class StocktakingHasNoProductsException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Stocktaking has no products');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1022;
    }
}
