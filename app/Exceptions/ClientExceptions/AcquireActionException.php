<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use Voronkovich\SberbankAcquiring\Exception\ActionException;

class AcquireActionException extends ClientException
{
    /**
     * @var int
     */
    protected int $acquireCode = 0;

    /**
     * @param \Voronkovich\SberbankAcquiring\Exception\ActionException $exception
     *
     * @return static
     */
    public static function fromActionException(ActionException $exception)
    {
        return new static($exception->getMessage(), $exception->getCode());
    }

    /**
     *
     */
    public function __construct(string $message, int $acquireCode = 0)
    {
        $this->acquireCode = $acquireCode;
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 100000 + $this->acquireCode;
    }
}
