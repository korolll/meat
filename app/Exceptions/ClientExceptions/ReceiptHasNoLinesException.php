<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class ReceiptHasNoLinesException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Receipt has no lines/products');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1020;
    }
}
