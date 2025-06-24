<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderTooManyBonusProvided extends ClientException
{
    /**
     * @var int
     */
    private int $maxBonuses;

    /**
     *
     */
    public function __construct(int $maxBonuses)
    {
        parent::__construct("There are too many bonuses to pay");
        $this->maxBonuses = $maxBonuses;
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2008;
    }

    /**
     * @return int
     */
    public function getMaxBonuses(): int
    {
        return $this->maxBonuses;
    }
}
