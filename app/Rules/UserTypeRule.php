<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UserTypeRule implements Rule
{
    /**
     * @var string
     */
    protected $allowedUserType;
    /**
     * @var string
     */
    protected $errorUuid;

    /**
     * Create a new rule instance.
     *
     * @param $allowedUserType
     */
    public function __construct($allowedUserType)
    {
        $this->allowedUserType = is_string($allowedUserType) ? [$allowedUserType] : $allowedUserType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->errorUuid = $value;

        return User::where([$attribute => $value])
            ->whereIn('user_type_id', $this->allowedUserType)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "User with UUID: '{$this->errorUuid}' not a '{$this->allowedUserType}' type";
    }
}
