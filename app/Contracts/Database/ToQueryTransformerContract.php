<?php

namespace App\Contracts\Database;

interface ToQueryTransformerContract
{
    /**
     * @param string $query
     * @return null|string
     */
    public function transform(string $query): ?string;
}
