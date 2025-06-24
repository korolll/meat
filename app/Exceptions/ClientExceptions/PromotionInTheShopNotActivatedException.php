<?php


namespace App\Exceptions\ClientExceptions;


use App\Exceptions\ClientException;

class PromotionInTheShopNotActivatedException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Promotion "I\'m In The Shop" is not activated');
    }

    public function getExceptionCode(): int
    {
        return 1030;
    }
}