<?php

namespace App\Services\Framework\Notifications\Channels;

use App\Services\Framework\Notifications\Messages\SmsMessage;
use App\Services\Integrations\StreamTelecom\StreamTelecomClient;

class StreamTelecomChannel extends SmsChannel
{
    /**
     * @var StreamTelecomClient
     */
    protected $client;

    /**
     * @param StreamTelecomClient $client
     */
    public function __construct(StreamTelecomClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $to
     * @param SmsMessage $message
     * @throws \App\Exceptions\TealsyException
     */
    protected function sendMessage($to, SmsMessage $message)
    {
        $this->client->send($to, $message->content);
    }
}
