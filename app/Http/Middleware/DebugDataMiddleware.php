<?php

namespace App\Http\Middleware;

use App\Services\Debug\DebugDataCollector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugDataMiddleware
{
    public function handle(Request $request, \Closure $next, ...$args): Response
    {
        $val = $request->query->get('admin_debug');
        if ($val !== config('app.admin_debug_key') || ! $request->expectsJson()) {
            return $next($request);
        }

        /** @var DebugDataCollector $collector */
        $collector = app(DebugDataCollector::class);
        $collector->enableQueryLog();

        /** @var Response $response */
        $response = $collector->measure('Middlewares', function () use ($request, $next) {
            return $next($request);
        });

        $collectedData = $collector->getDebugData();
        if ($collectedData) {
            $data = $response->getContent();
            $data = json_decode($data, true);
            $data['debug'] = $collectedData;

            if ($response instanceof JsonResponse) {
                $response->setData($data);
            } else {
                $response->setContent(json_encode($data));
            }
        }

        return $response;
    }
}
