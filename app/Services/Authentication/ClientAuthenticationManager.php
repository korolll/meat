<?php

namespace App\Services\Authentication;

use App\Models\Client;

class ClientAuthenticationManager implements ClientAuthenticationManagerContract
{
    /**
     * Максимальное количество активных токенов аутентификации
     */
    const CLIENT_AUTHENTICATION_TOKENS_MAX_COUNT = 2;

    /**
     * @param Client $client
     * @return string
     */
    public function generateAuthenticationCode(Client $client)
    {
        $code = $client->isDefaultClient() ? $this->getDefaultClientPhoneCode() : rand(1000, 9999);

        $authenticationCode = $client->clientAuthenticationCodes()->create(compact('code'));

        return $authenticationCode->code;
    }

    /**
     * @param Client $client
     * @param string $code
     * @return bool
     */
    public function validateAuthenticationCode(Client $client, $code)
    {
        $exists = $client->clientAuthenticationCodes()->where('code', $code)->exists();

        if ($exists) {
            $this->purgeAuthenticationCodes($client);
        }

        return $exists;
    }

    /**
     * @param Client $client
     * @return string
     */
    public function generateAuthenticationToken(Client $client)
    {
        $authenticationToken = $client->clientAuthenticationTokens()->create();

        $this->purgeAuthenticationTokens($client);

        return $authenticationToken->uuid;
    }

    /**
     * @param Client $client
     * @return int
     */
    protected function purgeAuthenticationCodes(Client $client)
    {
        return $client->clientAuthenticationCodes()->delete();
    }

    /**
     * @param Client $client
     * @return int
     */
    protected function purgeAuthenticationTokens(Client $client)
    {
        $validTokens = $client->clientAuthenticationTokens()->select('uuid')->orderByDesc('created_at')->limit(
            static::CLIENT_AUTHENTICATION_TOKENS_MAX_COUNT
        )->getQuery();

        return $client->clientAuthenticationTokens()->whereNotIn('uuid', $validTokens)->delete();
    }

    protected function getDefaultClientPhoneCode(): string
    {
        return config('auth.default_client_phone_code');
    }
}
