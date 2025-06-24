<?php

namespace App\Notifications\Drivers\API;

use App\Models\Driver;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DriverRegistered extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var Driver
     */
    public $driver;

    /**
     * @var string
     */
    public $password;

    /**
     * DriverRegistered constructor.
     * @param Driver $driver
     * @param string $password
     */
    public function __construct(Driver $driver, string $password)
    {
        $this->driver = $driver;
        $this->password = $password;
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->makeMail();
    }

    /**
     * @return MailMessage
     */
    protected function makeMail()
    {
        $mail = new MailMessage;
        return $mail->view('emails.drivers.registered', [
            'login' => $this->driver->email,
            'password' => $this->password,
        ]);
    }
}
