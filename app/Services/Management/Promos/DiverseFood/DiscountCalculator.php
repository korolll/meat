<?php

namespace App\Services\Management\Promos\DiverseFood;


use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoDiverseFoodClientStat;
use Carbon\CarbonInterface;

class DiscountCalculator implements DiscountCalculatorInterface
{
    /**
     * @var \App\Services\Management\Promos\DiverseFood\SettingFinderInterface
     */
    private SettingFinderInterface $finder;

    /**
     * @param \App\Services\Management\Promos\DiverseFood\SettingFinderInterface $finder
     */
    public function __construct(SettingFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param \Carbon\CarbonInterface|null $month
     */
    public function calculate(?CarbonInterface $month = null): void
    {
        $month = $month ?: now()->startOfMonth()->subDay();

        /** @noinspection PhpSuspiciousNameCombinationInspection */
        $nextMonth = $month->endOfMonth()->addDay();
        $startOfNextMonth = $nextMonth->startOfMonth();
        $endOfNextMonth = $nextMonth->endOfMonth();

        $query = PromoDiverseFoodClientStat::query()->where('month', $month->format('Y-m'));
        $query->each(function (PromoDiverseFoodClientStat $stat) use ($startOfNextMonth, $endOfNextMonth) {
            $discount = $this->getDiscount($stat);
            if ($discount > 0.0) {
                $row = new PromoDiverseFoodClientDiscount([
                    'discount_percent' => $discount,
                    'start_at' => $startOfNextMonth,
                    'end_at' => $endOfNextMonth
                ]);
                $row->client_uuid = $stat->client_uuid;
                $row->save();
            }
        });
    }

    /**
     * @param \App\Models\PromoDiverseFoodClientStat $stat
     *
     * @return float
     */
    public function getDiscount(PromoDiverseFoodClientStat $stat): float
    {
        $setting = $this->finder->findByStat($stat);
        if ($setting) {
            return $setting->discount_percent;
        }

        return 0.0;
    }
}
