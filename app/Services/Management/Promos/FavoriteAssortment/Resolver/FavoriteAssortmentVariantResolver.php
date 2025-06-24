<?php

namespace App\Services\Management\Promos\FavoriteAssortment\Resolver;

use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PromoFavoriteAssortmentSetting;
use App\Models\Receipt;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class FavoriteAssortmentVariantResolver implements FavoriteAssortmentVariantResolverInterface
{
    /**
     * @param \Carbon\CarbonInterface|null $moment
     * @param array                        $clientUuids
     * @param bool                         $force
     *
     * @throws \Throwable
     */
    public function resolve(?CarbonInterface $moment = null, array $clientUuids = [], bool $force = false): void
    {
        $moment = $moment ?: now();
        // Load current option
        $option = PromoFavoriteAssortmentSetting::first();
        if (! $option) {
            return;
        }

        $mainQuery = $this->makeMainQuery($option, $moment, $clientUuids, $force);
        $this->createRows($option, $mainQuery);
    }

    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $option
     * @param \Illuminate\Database\Query\Builder         $mainQuery
     *
     * @throws \Throwable
     */
    protected function createRows(PromoFavoriteAssortmentSetting $option, Builder $mainQuery): void
    {
        $format = DB::getQueryGrammar()->getDateFormat();
        $rows = $mainQuery->get();
        $cachedParsedDays = [];

        /** @var \StdClass $row */
        foreach ($rows as $row) {
            $date = $row->max_created_at_date;
            if (! isset($cachedParsedDays[$date])) {
                $parsed = Date::createFromFormat('Y-m-d', $date);
                $parsed = $parsed->addDays($option->number_of_active_days)->endOfDay();
                $cachedParsedDays[$date] = $parsed->format($format);
            }

            $parsed = $cachedParsedDays[$date];
            DB::transaction(function() use ($row, $parsed) {
                ClientPromoFavoriteAssortmentVariant::updateOrCreate(
                    ['client_uuid' => $row->client_uuid],
                    ['can_be_activated_till' => $parsed],
                );
            });
        }
    }

    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $option
     * @param \Carbon\CarbonInterface|null               $moment
     * @param array                                      $clientUuids
     * @param bool                                       $force
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function makeMainQuery(PromoFavoriteAssortmentSetting $option, ?CarbonInterface $moment = null, array $clientUuids = [], bool $force = false)
    {
        $from = $moment->subDays($option->number_of_sum_days)->startOfDay();
        $to = $moment->endOfDay();

        $orderQuery = $this->makeRequestForOrderTable($from, $to, $clientUuids);
        $receiptQuery = $this->makeRequestForReceipts($from, $to, $clientUuids);

        $union = $orderQuery->unionAll($receiptQuery);
        $mainQuery = DB::query()
            ->from($union, 'union_table')
            ->select([
                'union_table.client_uuid',
                DB::raw('MAX(union_table.max_created_at)::date as max_created_at_date')
            ])
            ->groupBy('union_table.client_uuid')
            ->havingRaw('SUM(union_table.sum) > ?', [$option->threshold_amount]);

        if (! $force) {
            // Exclude updated today
            $toExcludeIds = $this->loadAlreadyUpdatedClientUuid();
            if ($toExcludeIds->isNotEmpty()) {
                $mainQuery->whereNotIn('client_uuid', $toExcludeIds);
            }
        }

        return $mainQuery;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function loadAlreadyUpdatedClientUuid()
    {
        return ClientPromoFavoriteAssortmentVariant::query()
            ->whereDate('updated_at', now())
            ->select('client_uuid')
            ->pluck('client_uuid');
    }

    /**
     * @param \Carbon\CarbonInterface $from
     * @param \Carbon\CarbonInterface $to
     * @param array                   $clientUuids
     *
     * @return \App\Models\Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function makeRequestForOrderTable(CarbonInterface $from, CarbonInterface $to, array $clientUuids = [])
    {
        $query = Order::query()
            ->select([
                DB::raw('SUM(orders.total_price_for_products_with_discount) as sum'),
                DB::raw('MAX(orders.created_at) as max_created_at'),
                'orders.client_uuid'
            ])
            ->where('orders.order_status_id', OrderStatus::ID_DONE)
            ->whereBetween('orders.created_at', [
                $from,
                $to
            ])
            ->groupBy('orders.client_uuid');

        if ($clientUuids) {
            $query->whereIn('orders.client_uuid', $clientUuids);
        }

        return $query;
    }

    /**
     * @param \Carbon\CarbonInterface $from
     * @param \Carbon\CarbonInterface $to
     * @param array                   $clientUuids
     *
     * @return \App\Models\Receipt|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function makeRequestForReceipts(CarbonInterface $from, CarbonInterface $to, array $clientUuids = [])
    {
        $query = Receipt::query()
            ->select([
                DB::raw('SUM(receipts.total) as sum'),
                DB::raw('MAX(receipts.created_at) as max_created_at'),
                'loyalty_cards.client_uuid'
            ])
            ->join('loyalty_cards', 'loyalty_cards.uuid', 'receipts.loyalty_card_uuid')
            ->whereBetween('receipts.created_at', [
                $from,
                $to
            ])
            ->groupBy('loyalty_cards.client_uuid');

        if ($clientUuids) {
            $query->whereIn('loyalty_cards.client_uuid', $clientUuids);
        }

        return $query;
    }
}
