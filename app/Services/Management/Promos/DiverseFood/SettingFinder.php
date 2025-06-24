<?php

namespace App\Services\Management\Promos\DiverseFood;

use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodSettings;
use Illuminate\Database\Eloquent\Collection;

class SettingFinder implements SettingFinderInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected Collection $settings;

    /**
     *
     */
    public function __construct()
    {
        $this->settings = PromoDiverseFoodSettings::enabled()
            // сортируем в пользу клиента
            ->orderByDesc('discount_percent')
            ->get();
    }

    /**
     * @param int $purchasesCount
     * @param int $ratedCount
     *
     * @return \App\Models\PromoDiverseFoodSettings|null
     */
    public function find(int $purchasesCount, int $ratedCount): ?PromoDiverseFoodSettings
    {
        /** @var PromoDiverseFoodSettings $setting */
        foreach ($this->settings as $setting) {
            if (
                $purchasesCount >= $setting->count_purchases
                && $ratedCount >= $setting->count_rating_scores
            ) {
                return $setting;
            }
        }

        return null;
    }

    /**
     * @param \App\Models\PromoDiverseFoodClientStat $stat
     *
     * @return \App\Models\PromoDiverseFoodSettings|null
     */
    public function findByStat(PromoDiverseFoodClientStat $stat): ?PromoDiverseFoodSettings
    {
        return $this->find($stat->purchased_count, $stat->rated_count);
    }

    /**
     * @return \App\Models\PromoDiverseFoodSettings|null
     */
    public function getFirstLevel(): ?PromoDiverseFoodSettings
    {
        return $this->settings->last();
    }
}
