<?php

namespace App\Exceptions;

use App\Services\Integrations\Frontol\FrontolBadRequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ClientException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @param \Throwable $exception
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($this->shouldReport($exception) && !$this->isHttpException($exception) && !config('app.debug')) {
            $exception = new HttpException(500, 'Whoops!');
        }

        return parent::render($request, $exception);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param \Throwable $e
     * @return \Exception
     */
    protected function prepareException(Throwable $e)
    {
        if (!config('app.debug')) {
            if ($e instanceof ModelNotFoundException || $e instanceof SubstituteBindingsException) {
                $e = new HttpResponseException(response('', Response::HTTP_NOT_FOUND));
            }
        }

        return parent::prepareException($e);
    }

    /**
     * @param \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        if ($e instanceof FrontolBadRequestException) {
            return [
                'code' => $e->getCode(),
                'error' => $e->getMessage()
            ];
        }

        $array = parent::convertExceptionToArray($e);

        if ($e instanceof ClientException) {
            $array = array_merge($array, ['code' => $e->getCode()]);
        }

        if ($e instanceof AdditionalHttpDataExceptionInterface) {
            $array = array_merge($array, $e->getAdditionalResponseData());
        }

        return $array;
    }
}
