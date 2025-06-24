<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class PaymentVendorSettingInvalidConfigException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Invalid config');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1031;
    }
}
