<?php

namespace App\Services\Framework\Auth;

use App\Services\Framework\Contracts\Auth\TokenAuthenticatable;
use Illuminate\Auth\EloquentUserProvider as BaseEloquentUserProvider;
use Illuminate\Support\Arr;

class EloquentUserProvider extends BaseEloquentUserProvider
{
    /**
     * @param array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (count($credentials) === 1 && ($token = Arr::get($credentials, 'api_token')) !== null) {
            $model = $this->createModel();

            if ($model instanceof TokenAuthenticatable) {
                return $model->hasAuthenticationToken($token)->first();
            }
        }

        return parent::retrieveByCredentials($credentials);
    }
}
