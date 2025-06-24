<?php

/**
 * @param string $key
 * @param array|null $default
 * @return array|null
 */
function env_array(string $key, ?array $default = []): ?array
{
    $value = env($key);

    if ($value === null) {
        return $default;
    }

    return array_values(array_filter(array_map('trim', explode(',', $value))));
}

/**
 * @param string $path
 * @param string|array|null $query
 * @return string
 */
function url_frontend($path = '/', $query = null)
{
    $root = rtrim(config('app.frontend.url'), '/');
    $path = ltrim($path, '/');

    if ($query) {
        if (is_array($query)) {
            $query = http_build_query($query);
        } else {
            $query = urlencode($query);
        }

        $path = $path . '?' . $query;
    }

    return $root . '/' . $path;
}

/**
 * @return \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null
 */
function user()
{
    return auth()->user();
}
