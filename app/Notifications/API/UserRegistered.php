<?php

namespace App\Notifications\API;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new notification instance.
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $typeName = $this->getTypeName();

        return (new MailMessage)
            ->subject("Заявка на регистрацию {$typeName} {$this->user->organization_name}")
            ->markdown('emails.users.about_registered', [
                'typeName' => $typeName,
                'user' => $this->user,
            ]);
    }

    /**
     * @return string
     */
    protected function getTypeName()
    {
        $name = 'пользователя';

        switch ($this->user->user_type_id) {
            case UserType::ID_STORE:
                $name = 'магазина';
                break;
            case UserType::ID_SUPPLIER:
                $name = 'поставщика';
                break;
            case UserType::ID_DELIVERY_SERVICE:
                $name = 'доставки';
                break;
            case UserType::ID_DISTRIBUTION_CENTER:
                $name = 'распределительного центра';
                break;
            case UserType::ID_LABORATORY:
                $name = 'лаборатории';
                break;
        }

        return $name;
    }
}
