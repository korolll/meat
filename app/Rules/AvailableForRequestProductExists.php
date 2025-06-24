<?php

namespace App\Rules;

use App\Contracts\Models\Product\MakeProductsAvailableForRequestQueryContract;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class AvailableForRequestProductExists implements Rule
{
    /**
     * Пользователь из контекста запроса
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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
        $productUuids = collect($value)->map(function($item) {
            return $item['product_uuid'];
        });
        $query = resolve(MakeProductsAvailableForRequestQueryContract::class)->make([
            'customer_user_uuid' => $this->user->uuid
        ]);
        $query->whereIn('uuid', $productUuids->all());
        return $query->count() === $productUuids->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.exists');
    }
}
