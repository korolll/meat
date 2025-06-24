<?php

namespace App\Notifications\API;

use App\Models\User;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class UserEmailConfirmation extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var User
     */
    public $user;

    /**
     * UserVerified constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        return $mail->markdown('emails.users.email_confirmation', [
            'email' => $this->user->email,
            'token' => $this->user->email_verify_token,
        ]);
    }
}
