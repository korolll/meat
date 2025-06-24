<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * UserRegistered constructor.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
