<?php

namespace App\Notifications\API;

use App\Models\User;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class UserResetPassword extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var string
     */
    public $token;

    /**
     * @param User $user
     * @param string $token
     */
    public function __construct(User $user, string $token)
    {
        $this->token = encrypt([
            'email' => $user->email,
            'token' => $token,
        ]);
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
        return (new MailMessage())
            ->subject('Восстановние пароля')
            ->line('Чтобы восстановить пароль к Тилси, перейдите по этой ссылке.')
            ->action('Восстановить пароль', url_frontend('/reset_password', $this->token));
    }
}
