<?php

namespace App\Services\Order;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderAnalyticsService
{
    public function getAverageTotalPriceWithDiscount(?Collection $orders): float
    {
        $totalDiscountPrice = 0;
        foreach($orders as $order) {
            $totalDiscountPrice += $order['total_price_for_products_with_discount'];
        }
        return round($totalDiscountPrice / $orders->count(), 2);
    }

    public function getOrdersDoneForPeriod(string $startDate, string $endDate)
    {
        return Order::where(['order_status_id' => 'done'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();
    }

    public function getFrequencyRepeatedPurchases(int $count, string $startDate, string $endDate): float
    {
        if (empty($count)) {
            return 0.0;
        }

        $userCount = Order::select('store_user_uuid')
            ->where('order_status_id', '=', 'done')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->distinct()
            ->count('store_user_uuid');

        if ($userCount === 0) {
            return 0.0;
        }

        return round($count / $userCount, 2);
    }

    public function getLTV(
        float $averageTotalPriceWithDiscount,
        float $frequencyRepeatedPurchases,
        string $startDate,
        string $endDate
    ): float {
        $periodInMonths = $this->getPeriodForDatesInMonths($startDate, $endDate);
        return round($periodInMonths * $averageTotalPriceWithDiscount * $frequencyRepeatedPurchases, 2);
    }

    public function getAverageOrderProductsCount($orders): float
    {
        $totalOrderProductsCount = 0;
        foreach ($orders as $order) {
            $totalOrderProductsCount += $order->orderProducts()->get()->count();
        }

        return round($totalOrderProductsCount / $orders->count(), 2);
    }

    private function getPeriodForDatesInMonths(string $startDate, string $endDate): int
    {
        $d1 = new \DateTimeImmutable($startDate);
        $d2 = new \DateTimeImmutable($endDate);
        $Months = $d2->diff($d1);

        return (($Months->y) * 12) + ($Months->m);
    }
}
