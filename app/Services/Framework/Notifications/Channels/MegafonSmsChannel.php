<?php


namespace App\Services\Framework\Notifications\Channels;


use App\Services\Framework\Notifications\Messages\SmsMessage;
use App\Services\Integrations\Megafon\MegafonSmsClient;

class MegafonSmsChannel extends SmsChannel
{
    /**
     * @var MegafonSmsClient
     */
    protected $client;

    /**
     * @param \App\Services\Integrations\Megafon\MegafonSmsClient $client
     */
    public function __construct(MegafonSmsClient $client)
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