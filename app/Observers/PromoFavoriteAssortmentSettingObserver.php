<?php

namespace App\Observers;

use App\Jobs\ResolveClientFavoriteAssortmentVariantJob;
use App\Models\PromoFavoriteAssortmentSetting;

class PromoFavoriteAssortmentSettingObserver
{
    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $setting
     */
    public function updated(PromoFavoriteAssortmentSetting $setting)
    {
        $attrs = [
            'number_of_sum_days',
            'threshold_amount',
        ];
        if ($setting->wasChanged($attrs)) {
            ResolveClientFavoriteAssortmentVariantJob::dispatch([], true);
        }
    }
}
