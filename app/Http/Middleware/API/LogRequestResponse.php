<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequestResponse
{
    protected $channel = '';

    /**
     * @param          $request
     * @param \Closure $next
     * @param string   $channel
     *
     * @return mixed
     */
    public function handle($request, Closure $next, string $channel)
    {
        $this->channel = $channel;
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function terminate($request, $response)
    {
        if (config('app.debug') && $this->channel) {
            Log::channel($this->channel)->debug('Request log: ', [
                'request' => $request,
                'response' => [
                    'body' => $response->getContent(),
                    'code' => $response->getStatusCode()
                ]
            ]);
        }
    }
}
