<?php

namespace App\Services\Integrations\Frontol;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FrontolBadRequestException extends HttpException
{
    /**
     * @param string $message
     * @param int    $code
     *
     * @return static
     */
    public static function make(string $message, int $code = -1): self
    {
        return new static(200, $message, null, [], $code);
    }
}
