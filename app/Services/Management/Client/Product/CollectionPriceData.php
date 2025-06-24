<?php

namespace App\Services\Management\Client\Product;

use Illuminate\Support\Arr;

class CollectionPriceData implements CollectionPriceDataInterface
{
    private float $totalDiscount;
    private float $totalPriceWithDiscount;
    private float $totalWeight;
    private float $totalQuantity;
    private int $totalBonus;
    private int $paidBonus;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->totalDiscount = (float)Arr::get($data, 'total_discount', 0);
        $this->totalPriceWithDiscount = (float)Arr::get($data, 'total_price_with_discount', 0);
        $this->totalWeight = (float)Arr::get($data, 'total_weight', 0);
        $this->totalQuantity = (float)Arr::get($data, 'total_quantity', 0);
        $this->totalBonus = (int)Arr::get($data, 'total_bonus', 0);
        $this->paidBonus = (int)Arr::get($data, 'paid_bonus', 0);
    }

    /**
     * @return float
     */
    public function getTotalDiscount(): float
    {
        return $this->totalDiscount;
    }

    /**
     * @return float
     */
    public function getTotalPriceWithDiscount(): float
    {
        return $this->totalPriceWithDiscount;
    }

    /**
     * @return float
     */
    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    /**
     * @return float
     */
    public function getTotalQuantity(): float
    {
        return $this->totalQuantity;
    }

    /**
     * @return int
     */
    public function getTotalBonus(): int
    {
        return $this->totalBonus;
    }

    /**
     * @return int
     */
    public function getPaidBonus(): int
    {
        return $this->paidBonus;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'total_discount' => $this->totalDiscount,
            'total_price_with_discount' => $this->totalPriceWithDiscount,
            'total_weight' => $this->totalWeight,
            'total_quantity' => $this->totalQuantity,
            'total_bonus' => $this->totalBonus,
            'paid_bonus' => $this->paidBonus,
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
