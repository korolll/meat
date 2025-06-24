<?php

namespace App\Services\Integrations\Atol;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AtolOnlineClient implements AtolOnlineClientInterface
{
    const GET_TOKEN_PATH = 'getToken';
    const SELL_PATH = 'sell';
    const SELL_REFUND_PATH = 'sell_refund';

    /**
     * @var \GuzzleHttp\Client
     */
    private Client $client;
    private ?string $cachedToken;

    private string $user;
    private string $groupCode;
    private string $password;

    /**
     * @param \GuzzleHttp\Client $client
     * @param array              $config
     */
    public function __construct(Client $client, array $config)
    {
        $this->client = $client;

        $this->user = (string)Arr::get($config, 'user');
        $this->password = (string)Arr::get($config, 'password');
        $this->groupCode = (string)Arr::get($config, 'group_code');

        if (! $this->user || ! $this->password|| ! $this->groupCode) {
            throw new \InvalidArgumentException('Invalid $config provided');
        }

        $this->cachedToken = Arr::get($config, 'cached_token');
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sell(array $params): array
    {
        return $this->groupRequest('POST', static::SELL_PATH, [
            RequestOptions::JSON => $params
        ]);
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sellRefund(array $params): array
    {
        return $this->groupRequest('POST', static::SELL_REFUND_PATH, [
            RequestOptions::JSON => $params
        ]);
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function refreshToken()
    {
        $token = $this->getNewToken();
        if (! $token) {
            throw new \Exception('Cant get a new token');
        }

        $this->cachedToken = $token;
    }


    /**
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getNewToken(): ?string
    {
        $body = [
            'login' => $this->user,
            'pass' => $this->password
        ];

        $result = $this->request('POST', static::GET_TOKEN_PATH, [
            RequestOptions::JSON => $body
        ]);

        return Arr::get($result, 'token');
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function groupRequest(string $method, string $path, array $params = [])
    {
        $path = $this->groupCode . '/' . $path;
        return $this->request($method, $path, $params);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method, string $path, array $params = [])
    {
        if ($this->cachedToken === null && $path !== static::GET_TOKEN_PATH) {
            $this->refreshToken();
        }

        if ($this->cachedToken) {
            $params = array_replace_recursive($params, [
                RequestOptions::HEADERS => [
                    'Token' => $this->cachedToken
                ]
            ]);
        }

        try {
            $response = $this->client->request($method, $path, $params);
            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (ClientException $exception) {
            $responseExc = $exception->getResponse();
            $respData = $responseExc->getBody()->getContents();
            if ($responseExc->getStatusCode() === 401 && Str::contains($respData, 'ExpiredToken')) {
                $this->cachedToken = null;
                return $this->request($method, $path, $params);
            }

            throw $exception;
        }
    }
}
