<?php

namespace App\Services\Authentication;

use App\Models\Client;

interface ClientAuthenticationManagerContract
{
    /**
     * @param Client $client
     * @return string
     */
    public function generateAuthenticationCode(Client $client);

    /**
     * @param Client $client
     * @param string $code
     * @return bool
     */
    public function validateAuthenticationCode(Client $client, $code);

    /**
     * @param Client $client
     * @return string
     */
    public function generateAuthenticationToken(Client $client);
}
