<?php

namespace App\Services\Framework\Contracts\Auth;

use Illuminate\Database\Eloquent\Builder;

interface TokenAuthenticatable
{
    /**
     * @param Builder $query
     * @param string $token
     * @return Builder
     */
    public function scopeHasAuthenticationToken(Builder $query, $token);
}
