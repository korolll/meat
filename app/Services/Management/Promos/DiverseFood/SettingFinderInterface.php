<?php

namespace App\Services\Management\Promos\DiverseFood;


use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodSettings;

interface SettingFinderInterface
{
    public function find(int $purchasesCount, int $ratedCount): ?PromoDiverseFoodSettings;

    public function findByStat(PromoDiverseFoodClientStat $stat): ?PromoDiverseFoodSettings;

    public function getFirstLevel(): ?PromoDiverseFoodSettings;
}
