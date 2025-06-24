<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\AdditionalHttpDataExceptionInterface;

class AcquireActionCodeException extends AcquireActionException implements AdditionalHttpDataExceptionInterface
{
    /**
     * @var string
     */
    private string $specialCode;

    /**
     *
     */
    public function __construct(string $message, int $acquireCode = 100, string $specialCode = '')
    {
        parent::__construct($message, $acquireCode);
        $this->specialCode = $specialCode;
    }

    /**
     * @return string[]
     */
    public function getAdditionalResponseData(): array
    {
        return [
            'special_code' => $this->specialCode
        ];
    }
}
