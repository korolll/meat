<?php

namespace App\Services\Integrations\StreamTelecom;

use App\Exceptions\ServerException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class StreamTelecomClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $from;

    /**
     * @param Client $client
     * @param string $username
     * @param string $password
     * @param string $from
     */
    public function __construct(Client $client, $username, $password, $from)
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
    }

    /**
     * @param string $to
     * @param string $text
     * @throws \App\Exceptions\TealsyException
     */
    public function send($to, $text)
    {
        try {
            $this->client->get('http://gateway.api.sc/get', [
                'query' => [
                    'user' => $this->username,
                    'pwd' => $this->password,
                    'sadr' => $this->from,
                    'dadr' => $to,
                    'text' => $text,
                ],
            ]);
        } catch (RequestException $e) {
            throw new ServerException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
