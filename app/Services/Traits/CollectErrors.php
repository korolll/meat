<?php


namespace App\Services\Traits;

use App\Exceptions\ClientException;
use Exception;

/**
 * Trait CollectErrors
 *
 * @package App\Services\Traits
 */
trait CollectErrors
{
    public static $EXCEPTION_CODE = 10000;
    /**
     * @var array
     */
    private $errors = [];
    /**
     * Вызывать ли исключения на каждую ошибку по отдельности или передавать массив ошибок в одном исключении
     * @var bool
     */
    private $isSendErrorsArray = false;

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param Exception $exception
     *
     * @return static
     */
    private function addError(Exception $exception)
    {
        $this->errors[] = [
            'message' => $exception->getMessage(),
            'code' => method_exists($exception, 'getExceptionCode') ? $exception->getExceptionCode() : 0,
        ];

        return $this;
    }

    private function mergeErrors(array $errors)
    {
        return $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * @return bool
     */
    public function getIsSendErrorsArray(): bool
    {
        return $this->isSendErrorsArray;
    }

    /**
     * @param bool $isSendErrorsArray
     * @return static
     */
    public function setIsSendErrorsArray(bool $isSendErrorsArray)
    {
        $this->isSendErrorsArray = $isSendErrorsArray;

        return $this;
    }

    /**
     * @param Exception $exception
     * @return CollectErrors
     * @throws Exception
     */
    private function setOrThrowException(Exception $exception)
    {
        if ($this->isSendErrorsArray) {
            $this->addError($exception);

            return $this;
        }

        throw $exception;
    }

    public function throwExceptionWithErrors()
    {
        throw new class(json_encode($this->getErrors())) extends ClientException
        {
            /**
             * @return int
             */
            public function getExceptionCode(): int
            {
                return CollectErrors::$EXCEPTION_CODE;
            }
        };
    }
}
