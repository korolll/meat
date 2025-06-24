<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class FileUploadFailedException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('File upload failed');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1009;
    }
}
