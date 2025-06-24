<?php

namespace App\Services\Framework\CacheProfile;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\ResponseCache\CacheProfiles\BaseCacheProfile;
use Symfony\Component\HttpFoundation\Response;

class CacheSuccessfulGetRequestsByConfig extends BaseCacheProfile
{
    const CONF_INPUT_VAR_NAME = 'cache_input_var_name';
    const CONF_INPUT_VAR_CONFIGS = 'cache_input_var_configs';

    const CONF_CACHE_SECONDS = 'cache_seconds';

    /**
     * @var array
     */
    protected array $config;

    /**
     * CacheSuccessfulGetRequestsByConfig constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function shouldCacheRequest(Request $request): bool
    {
        if ($this->isRunningInConsole()) {
            return false;
        }

        $config = $this->getRequestConfig($request);
        if ($config === null) {
            return false;
        }

        return $request->isMethod('get');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return bool
     */
    public function shouldCacheResponse(Response $response): bool
    {
        if (! $this->hasCacheableResponseCode($response)) {
            return false;
        }

        if (! $this->hasCacheableContentType($response)) {
            return false;
        }

        return true;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \DateTime
     */
    public function cacheRequestUntil(Request $request): DateTime
    {
        $config = $this->getRequestConfig($request);
        $cacheSeconds = Arr::get($config, static::CONF_CACHE_SECONDS);
        if (! $cacheSeconds) {
            return parent::cacheRequestUntil($request);
        }

        return Carbon::now()->addSeconds($cacheSeconds);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function useCacheNameSuffix(Request $request): string
    {
        $userId = parent::useCacheNameSuffix($request);
        $config = $this->getBaseRequestConfig($request);
        $cacheInputValue = Arr::get($config, static::CONF_INPUT_VAR_NAME);

        if ($cacheInputValue) {
            $userId .= '_' . $request->get($cacheInputValue);
        }

        return $userId;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return bool
     */
    protected function hasCacheableResponseCode(Response $response): bool
    {
        if ($response->isSuccessful()) {
            return true;
        }

        if ($response->isRedirection()) {
            return true;
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return bool
     */
    protected function hasCacheableContentType(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        if (Str::startsWith($contentType, 'text/')) {
            return true;
        }

        if (Str::contains($contentType, ['/json', '+json'])) {
            return true;
        }

        return false;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array|null
     */
    protected function getRequestConfig(Request $request): ?array
    {
        $baseConfig = $this->getBaseRequestConfig($request);
        if (! $baseConfig) {
            return null;
        }

        $cacheInputValue = Arr::get($baseConfig, static::CONF_INPUT_VAR_NAME);
        if (! $cacheInputValue) {
            return $baseConfig;
        }

        $inpValue = $request->get($cacheInputValue);
        $inpVarConfigs = (array)Arr::get($baseConfig, static::CONF_INPUT_VAR_CONFIGS, []);
        return Arr::get($inpVarConfigs, $inpValue);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array|null
     */
    protected function getBaseRequestConfig(Request $request): ?array
    {
        $keyName = 'cached_uri';
        if ($request->server->has($keyName)) {
            $path = $request->server->get($keyName);
        } else {
            /** @var \Illuminate\Routing\Route $route */
            $route = call_user_func($request->getRouteResolver());
            $path = $route->uri();
            $request->server->set($keyName, $path);
        }

        return Arr::get($this->config, $path);
    }
}
