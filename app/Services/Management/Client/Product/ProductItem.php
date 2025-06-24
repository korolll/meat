<?php

namespace App\Services\Management\Client\Product;

use App\Models\Product;
use Illuminate\Queue\SerializesModels;

class ProductItem implements ProductItemInterface
{
    use SerializesModels;

    private Product $product;
    private float $quantity;
    private int $paidBonus;

    /**
     * @param \App\Models\Product $product
     * @param float               $quantity
     * @param int                 $paidBonus
     *
     * @return static
     */
    public static function create(Product $product, float $quantity, int $paidBonus = 0): self
    {
        return new static($product, $quantity, $paidBonus);
    }

    /**
     * @param \App\Models\Product $product
     * @param float               $quantity
     * @param int                 $paidBonus
     */
    public function __construct(Product $product, float $quantity, int $paidBonus = 0)
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->paidBonus = $paidBonus;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @return \App\Models\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getPaidBonus(): int
    {
        return $this->paidBonus;
    }

    /**
     * @param int $bonus
     *
     * @return \App\Services\Management\Client\Product\ProductItemInterface
     */
    public function setPaidBonus(int $bonus): ProductItemInterface
    {
        $this->paidBonus = $bonus;
        return $this;
    }
}
