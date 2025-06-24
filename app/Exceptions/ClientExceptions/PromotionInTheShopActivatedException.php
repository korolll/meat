<?php


namespace App\Exceptions\ClientExceptions;


use App\Exceptions\ClientException;

class PromotionInTheShopActivatedException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Promotion "I\'m In The Shop" has already been activated');
    }

    public function getExceptionCode(): int
    {
        return 1029;
    }
}