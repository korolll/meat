<?php

namespace App\Services\Money;


use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;
use Brick\Money\AbstractMoney;
use Brick\Money\Context\DefaultContext;
use Brick\Money\RationalMoney;


final class MoneyHelper
{
    private function __construct()
    {
    }

    /**
     * @param BigNumber|int|float|string $amount The monetary amount.
     *
     * @return RationalMoney
     */
    public static function of($amount): RationalMoney
    {
        return RationalMoney::of($amount, 'RUB');
    }

    /**
     * @param \Brick\Money\AbstractMoney $money
     * @param int                        $round
     *
     * @return float
     */
    public static function toFloat(AbstractMoney $money, int $round = RoundingMode::HALF_UP): float
    {
        if ($money instanceof RationalMoney) {
            $money = $money->to(new DefaultContext(), $round);
        }

        return $money->getAmount()->toFloat();
    }

    public static function round(\Brick\Money\AbstractMoney|int|float|string $amount, int $round = RoundingMode::HALF_UP)
    {
        $money = static::of($amount);
        return static::toFloat($money, $round);
    }

    /**
     * @param \Brick\Money\AbstractMoney|int|float|string $amount
     * @param int                                         $round
     *
     * @return int
     */
    public static function toKopek($amount, int $round = RoundingMode::HALF_UP): int
    {
        if (! $amount instanceof AbstractMoney) {
            $amount = MoneyHelper::of($amount);
        }

        $res = $amount->multipliedBy(100);
        return (int)MoneyHelper::toFloat($res, $round);
    }

    /**
     * @param \Brick\Money\AbstractMoney|float|int $money
     *
     * @return int
     */
    public static function toBonus($money): int
    {
        if (! $money instanceof AbstractMoney) {
            $money = MoneyHelper::of($money);
        }

        return (int)MoneyHelper::toFloat($money, RoundingMode::DOWN);
    }

    /**
     * @param float $percent
     * @param float $value
     *
     * @return float
     */
    public static function percentOf(float $percent, float $value): float
    {
        $result = MoneyHelper::of($value)
            ->multipliedBy($percent)
            ->dividedBy(100);

        return MoneyHelper::toFloat($result);
    }

    /**
     * @param float $percent
     * @param float $value
     *
     * @return \Brick\Money\RationalMoney
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public static function valueWithDiscount(float $percent, float $value)
    {
        $percent = MoneyHelper::percentOf($percent, $value);
        return MoneyHelper::of($percent)
            ->minus($value)
            ->dividedBy(-1);
    }
}
