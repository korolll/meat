<?php

namespace App\Services\Management\Client\Product\Discount;

use Illuminate\Database\Eloquent\Model;

class DiscountData implements DiscountDataInterface
{
    private float $price;
    private Model $discount;
    private bool $highPriority;

    /**
     * @param float                               $price
     * @param \Illuminate\Database\Eloquent\Model $discount
     * @param bool                                $highPriority
     *
     * @return static
     */
    public static function create(float $price, Model $discount, bool $highPriority = false): self
    {
        return new static($price, $discount, $highPriority);
    }

    /**
     * @param float                               $price
     * @param \Illuminate\Database\Eloquent\Model $discount
     * @param bool                                $highPriority
     */
    public function __construct(float $price, Model $discount, bool $highPriority = false)
    {
        $this->price = $price;
        $this->discount = $discount;
        $this->highPriority = $highPriority;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getDiscountModel(): Model
    {
        return $this->discount;
    }

    /**
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return $this->highPriority;
    }
}
