<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class AttributeNotFiltrableException extends ClientException
{
    /**
     * @param string $attribute
     */
    public function __construct(string $attribute)
    {
        parent::__construct("Attribute {$attribute} is not filterable");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1002;
    }
}
