<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVerified
{
    use Dispatchable, SerializesModels;

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
}
