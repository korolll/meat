<?php


namespace App\Services\Integrations\Megafon;


use App\Exceptions\ServerException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MegafonSmsClient
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
    public function __construct(Client $client, string $username, string $password, string $from)
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
    public function send(string $to, string $text)
    {
        $hash = base64_encode($this->username.':'.$this->password);
        $response = $this->client->post('https://a2p-api.megalabs.ru/sms/v1/sms', [
            'headers' => [
                'Authorization' => "Basic {$hash}",
                ],
            'json' => [
                'from' => $this->from,
                'to' => (int) $to,
                'message' => $text,
                ]
            ])->getBody();

        $result = json_decode($response, true);
        if ($result['result']['status']['code'] === 0) {
            $msgId = $result['result']['msg_id'];
            return $msgId;
        } else {
            throw new \Exception(json_encode($result['result']['status']));
        }
    }
}
