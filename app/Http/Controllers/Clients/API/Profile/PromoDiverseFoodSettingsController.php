<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoDiverseFoodSettingsResource;
use App\Services\Management\Promos\DiverseFood\SettingFinderInterface;


class PromoDiverseFoodSettingsController extends Controller
{
    /**
     * @return \App\Http\Resources\PromoDiverseFoodSettingsResource
     */
    public function futureLevel()
    {
        /** @var SettingFinderInterface $finder */
        $finder = app(SettingFinderInterface::class);
        /** @var \App\Models\PromoDiverseFoodClientStat $currentUserStat */
        $currentUserStat = $this->client->promoDiverseFoodClientStats()
            ->where('month', now()->format('Y-m'))
            ->first();

        if (! $currentUserStat) {
            return ['data' => null];
        }

        $found = $finder->findByStat($currentUserStat);
        if (! $found) {
            return ['data' => null];
        }

        return PromoDiverseFoodSettingsResource::make($found);
    }
}
