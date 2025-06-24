<?php

namespace App\Services\Management\ProductRequest;

use App\Exceptions\ClientExceptions\ExpectedDeliveryDateInvalidException;
use App\Exceptions\ClientExceptions\ExpectedDeliveryDateMustBeGreaterThanException;
use App\Services\Traits\CollectErrors;
use App\Models\Product;
use Carbon\CarbonInterface;

class ProductRequestExpectedDeliveryDateValidator implements ProductRequestExpectedDeliveryDateValidatorContract
{
    use CollectErrors;

    /**
     * Дата, к которой нужно добавить минимальный срок доставки продукта
     *
     * @var CarbonInterface|null
     */
    protected $startOfMinDeliveryDate;

    /**
     * @param CarbonInterface $startOfMinDeliveryDate
     * @return ProductRequestExpectedDeliveryDateValidatorContract
     */
    public function setStartOfMinDeliveryDate(CarbonInterface $startOfMinDeliveryDate): ProductRequestExpectedDeliveryDateValidatorContract
    {
        $this->startOfMinDeliveryDate = $startOfMinDeliveryDate;

        return $this;
    }

    /**
     * @return CarbonInterface
     */
    public function getStartOfMinDeliveryDate(): CarbonInterface
    {
        return $this->startOfMinDeliveryDate ?: now();
    }

    /**
     * @param array|Product[] $products
     * @return CarbonInterface
     */
    public function getMaxOfMinDeliveryDate(array $products): CarbonInterface
    {
        $dates = array_map(function (Product $product) {
            $date = $this->getStartOfMinDeliveryDate();

            return $date->addHours($product->min_delivery_time);
        }, $products);

        return max($dates);
    }

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @param array|Product[] $products
     * @return bool
     * @throws \App\Exceptions\TealsyException
     * @throws \Exception
     */
    public function validate(CarbonInterface $expectedDeliveryDate, array $products): bool
    {
        $minDate = $this->getMaxOfMinDeliveryDate($products);
        if ($minDate >= $expectedDeliveryDate) {
            $this->setOrThrowException(new ExpectedDeliveryDateMustBeGreaterThanException($minDate));
        }

        // Проверяем день отгрузки
        collect($products)->each(function (Product $product) use ($expectedDeliveryDate) {
            if (!in_array($expectedDeliveryDate->dayOfWeek, $product->delivery_weekdays, true)) {
                $this->setOrThrowException(new ExpectedDeliveryDateInvalidException($expectedDeliveryDate));
            }
        });

        return !$this->errors;
    }
}
