<?php

namespace App\Services\Integrations\Iiko;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IikoClient implements IikoClientInterface
{
    const V1 = 'api/1/';
    const GET_TOKEN_PATH = 'api/1/access_token';

    /**
     * @var \GuzzleHttp\Client
     */
    private Client $client;
    private ?string $apiKey;
    private ?string $cachedToken;

    /**
     * @param \GuzzleHttp\Client $client
     * @param string             $apiKey
     * @param string|null        $token
     */
    public function __construct(Client $client, ?string $apiKey = null, ?string $token = null)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->cachedToken = $token;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrganizations(): array
    {
        $path = static::V1 . 'organizations';
        $result = $this->request('POST', $path, [
            RequestOptions::JSON => ['returnAdditionalInfo' => true]
        ]);
        return (array)Arr::get($result, 'organizations', []);
    }

    /**
     * @param string $organizationId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMenu(string $organizationId): array
    {
        $path = static::V1 . 'nomenclature';
        $body = compact('organizationId');
        $result = $this->request('POST', $path, [
            RequestOptions::JSON => $body
        ]);

        return (array)$result;
    }

    /**
     * @param array $organizationIds
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getStopLists(array $organizationIds): array
    {
        $path = static::V1 . 'stop_lists';
        $body = compact('organizationIds');
        $result = $this->request('POST', $path, [
            RequestOptions::JSON => $body
        ]);

        return (array)$result;
    }

    /**
     * @param array $organizationIds
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getStopListsMap(array $organizationIds): array
    {
        $data = $this->getStopLists($organizationIds);
        $result = [];
        foreach ($data['terminalGroupStopLists'] as $stopList) {
            $organizationId = $stopList['organizationId'];
            if (! isset($result[$organizationId])) {
                $result[$organizationId] = [];
            }

            foreach ($stopList['items'] as $terminalData) {
                foreach ($terminalData['items'] as $stop) {
                    $result[$organizationId][$stop['productId']] = $stop['balance'];
                }
            }
        }

        return $result;
    }

    /**
     * @param array $organizationIds
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPaymentTypes(array $organizationIds): array
    {
        $path = static::V1 . 'payment_types';
        $body = compact('organizationIds');
        $result = $this->request('POST', $path, [
            RequestOptions::JSON => $body
        ]);

        return (array)Arr::get($result, 'paymentTypes', []);
    }

    /**
     * @param array $organizationIds
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrderTypes(array $organizationIds): array
    {
        $path = static::V1 . 'deliveries/order_types';
        $body = compact('organizationIds');
        $result = $this->request('POST', $path, [
            RequestOptions::JSON => $body
        ]);

        return (array)Arr::get($result, 'orderTypes', []);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOrder(array $data): array
    {
        $path = static::V1 . 'deliveries/create';
        return $this->request('POST', $path, [
            RequestOptions::JSON => $data
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
            'apiLogin' => $this->apiKey ?: ''
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
    protected function request(string $method, string $path, array $params = [])
    {
        if ($this->cachedToken === null && $path !== static::GET_TOKEN_PATH) {
            $this->refreshToken();
        }

        $params = array_replace_recursive($params, [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->cachedToken
            ]
        ]);

        try {
            $response = $this->client->request($method, $path, $params);
            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (ClientException $exception) {
            $responseExc = $exception->getResponse();
            $respData = $responseExc->getBody()->getContents();
            if ($responseExc->getStatusCode() === 401 && Str::contains($respData, 'expired')) {
                $this->cachedToken = null;
                return $this->request($method, $path, $params);
            }

            throw $exception;
        }
    }
}
