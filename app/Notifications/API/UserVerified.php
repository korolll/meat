<?php

namespace App\Notifications\API;

use App\Models\User;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class UserVerified extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var User
     */
    public $user;

    /**
     * @var null|string
     */
    public $comment;

    /**
     * UserVerified constructor.
     * @param User $user
     * @param null|string $comment
     */
    public function __construct(User $user, ?string $comment = null)
    {
        $this->user = $user;
        $this->comment = $comment;
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
        return $mail->markdown('emails.users.verified', [
            'success_verified' => $this->user->is_verified,
            'email' => $this->user->email,
            'comment' => $this->comment,
        ]);
    }
}
