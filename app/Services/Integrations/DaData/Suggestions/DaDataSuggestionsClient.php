<?php

namespace App\Services\Integrations\DaData\Suggestions;

use App\Contracts\Integrations\DaData\Suggestions\DaDataSuggestionsClientContract;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class DaDataSuggestionsClient implements DaDataSuggestionsClientContract
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzle;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param \GuzzleHttp\Client $guzzle
     * @param string $apiKey
     */
    public function __construct(Client $guzzle, string $apiKey)
    {
        $this->guzzle = $guzzle;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $query
     * @return Collection
     */
    public function banks(string $query): Collection
    {
        return $this->makeSuggestions('bank', $query);
    }

    /**
     * @param string $query
     * @return Collection
     */
    public function organizations(string $query): Collection
    {
        return $this->makeSuggestions('party', $query);
    }

    /**
     * @param string $suggestionType
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    private function makeSuggestions(string $suggestionType, string $query): Collection
    {
        $response = $this->sendRequest($suggestionType, compact('query'));
        $response = json_decode($response->getBody(), true);
        $response = Arr::get($response, 'suggestions', []);

        return collect($response);
    }

    /**
     * @param string $suggestionType
     * @param array $json
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendRequest(string $suggestionType, array $json): ResponseInterface
    {
        return $this->guzzle->post($this->makeUrl($suggestionType), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Token {$this->apiKey}",
            ],
            'json' => $json,
        ]);
    }

    /**
     * @param string $suggestionType
     * @return string
     */
    private function makeUrl(string $suggestionType): string
    {
        return 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/' . $suggestionType;
    }
}
