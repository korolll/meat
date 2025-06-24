<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class ClientException extends TealsyException implements HttpExceptionInterface
{
    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message, $this->getExceptionCode());
    }

    /**
     * @return int
     */
    abstract public function getExceptionCode(): int;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }
}
