<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class ProductRequestAlreadyHasDeliveryUserException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Product request is already has delivery user');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1017;
    }
}
