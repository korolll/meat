<?php

namespace App\Services\Management\Client\Product;

use App\Services\Money\MoneyHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class PriceData implements PriceDataInterface
{
    use SerializesModels;

    private float $priceWithDiscount;
    private float $discount;
    private float $totalAmountWithDiscount;
    private float $totalDiscount;
    private float $totalWeight;
    private float $totalQuantity;
    private int $totalBonus;
    private int $paidBonus;
    private float $fixedPaidBonus;
    private ?Model $discountModel;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->priceWithDiscount = (float)Arr::get($data, 'price_with_discount', 0);
        $this->discount = (float)Arr::get($data, 'discount', 0);
        $this->totalDiscount = (float)Arr::get($data, 'total_discount', 0);
        $this->totalAmountWithDiscount = (float)Arr::get($data, 'total_amount_with_discount', 0);
        $this->totalWeight = (float)Arr::get($data, 'total_weight', 0);
        $this->totalQuantity = (float)Arr::get($data, 'total_quantity', 0);
        $this->discountModel = Arr::get($data, 'discount_model');
        $this->totalBonus = (int)Arr::get($data, 'total_bonus', 0);
        $this->paidBonus = (int)Arr::get($data, 'paid_bonus', 0);
        $this->fixedPaidBonus = (float)Arr::get($data, 'fixed_paid_bonus', $this->paidBonus);
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @return float
     */
    public function getPriceWithDiscount(): float
    {
        return $this->priceWithDiscount;
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
    public function getTotalAmountWithDiscount(): float
    {
        return $this->totalAmountWithDiscount;
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
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getDiscountModel(): ?Model
    {
        return $this->discountModel;
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
     * @return float
     */
    public function getFixedPaidBonus(): float
    {
        return $this->fixedPaidBonus;
    }

    /**
     * @param \App\Services\Management\Client\Product\PriceDataInterface $data
     *
     * @return \App\Services\Management\Client\Product\PriceDataInterface
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function diff(PriceDataInterface $data): PriceDataInterface
    {
        $diffDiscount = MoneyHelper::of($data->getDiscount())->minus($this->discount);
        $diffDiscount = MoneyHelper::toFloat($diffDiscount);

        $diffPriceWithDiscount = MoneyHelper::of($data->getPriceWithDiscount())->minus($this->priceWithDiscount);
        $diffPriceWithDiscount = MoneyHelper::toFloat($diffPriceWithDiscount);

        $diffTotalAmountWithDiscount = MoneyHelper::of($data->getTotalAmountWithDiscount())->minus($this->totalAmountWithDiscount);
        $diffTotalAmountWithDiscount = MoneyHelper::toFloat($diffTotalAmountWithDiscount);

        $diffTotalDiscount = MoneyHelper::of($data->getTotalDiscount())->minus($this->totalDiscount);
        $diffTotalDiscount = MoneyHelper::toFloat($diffTotalDiscount);

        $diffWeight = $data->getTotalWeight() - $this->totalWeight;
        $diffQuantity = $data->getTotalQuantity() - $this->totalQuantity;
        $diffTotalBonus = $data->getTotalBonus() - $this->totalBonus;
        $diffPaidBonus = $data->getPaidBonus() - $this->paidBonus;

        return new PriceData([
            'price_with_discount' => $diffPriceWithDiscount,
            'discount' => $diffDiscount,
            'total_discount' => $diffTotalDiscount,
            'total_amount_with_discount' => $diffTotalAmountWithDiscount,
            'total_weight' => $diffWeight,
            'total_quantity' => $diffQuantity,
            'total_bonus' => $diffTotalBonus,
            'paid_bonus' => $diffPaidBonus,
            'discount_model' => $data->getDiscountModel()
        ]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'price_with_discount' => $this->priceWithDiscount,
            'discount' => $this->discount,
            'total_discount' => $this->totalDiscount,
            'total_amount_with_discount' => $this->totalAmountWithDiscount,
            'total_weight' => $this->totalWeight,
            'total_quantity' => $this->totalQuantity,
            'total_bonus' => $this->totalBonus,
            'paid_bonus' => $this->paidBonus,
            'discount_model' => $this->discountModel
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
