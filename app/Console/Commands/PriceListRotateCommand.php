<?php

namespace App\Console\Commands;

use App\Models\PriceList;
use App\Models\PriceListStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PriceListRotateCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'price-list:rotate';

    /**
     * @var string
     */
    protected $description = 'Выполняет цикл замены текущих прайс-листов будущими';

    /**
     * @return void
     */
    public function handle()
    {
        PriceList::future()->where('date_from', '<', now())->each(function (PriceList $priceList) {
            DB::transaction(function () use ($priceList) {
                $priceList->price_list_status_id = PriceListStatus::CURRENT;
                $priceList->save();

                $this->deleteOldPriceLists($priceList);
            });
        });
    }

    /**
     * @param \App\Models\PriceList $newPriceList
     */
    protected function deleteOldPriceLists(PriceList $newPriceList): void
    {
        $query = PriceList::whereUserUuid($newPriceList->user_uuid);
        if ($newPriceList->customer_user_uuid) {
            $query->where('customer_user_uuid', $newPriceList->customer_user_uuid);
        } else {
            $query->whereNull('customer_user_uuid');
        }

        $query
            ->where('uuid', '!=', $newPriceList->uuid)
            ->where('price_list_status_id', PriceListStatus::ARCHIVE)
            ->orderByDesc('date_till');

        $skip = 1;
        $counter = 0;
        $query->each(function (PriceList $list) use (&$counter, $skip) {
            $counter++;
            if ($counter <= $skip) {
                return;
            }

            $this->deletePriceList($list);
        });
    }

    /**
     * @param \App\Models\PriceList $priceList
     */
    protected function deletePriceList(PriceList $priceList): void
    {
        $priceList->products()->detach();
        $priceList->forceDelete();
    }
}
