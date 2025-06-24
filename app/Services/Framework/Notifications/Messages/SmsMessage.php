<?php

namespace App\Services\Framework\Notifications\Messages;

use App\Services\Framework\HasStaticMakeMethod;

class SmsMessage
{
    use HasStaticMakeMethod;

    /**
     * @var string
     */
    public $content;

    /**
     * @param string $content
     */
    public function __construct($content = '')
    {
        $this->content($content);
    }

    /**
     * @param string $content
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }
}
