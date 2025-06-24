<?php

namespace App\Exceptions;

interface AdditionalHttpDataExceptionInterface
{
    public function getAdditionalResponseData(): array;
}
