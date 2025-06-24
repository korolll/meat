<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoyaltyCardUpdateDiscountCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'loyalty-card:update-discount';

    /**
     * @var string
     */
    protected $description = 'Выполняет обновление скидок по картам лояльности';

    /**
     * @return void
     */
    public function handle()
    {
        $percentSubQuery = $this->makePercentSubQuery();

        DB::statement(
            "UPDATE loyalty_cards SET discount_percent = ({$percentSubQuery->toSql()})",
            $percentSubQuery->getBindings()
        );
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function makePercentSubQuery()
    {
        return DB::table('rating_scores')->select([
            DB::raw('LEAST(10, FLOOR(COUNT(rating_scores.uuid) / 10) + 3)'),
        ])->where([
            'rating_scores.rated_reference_type' => 'assortment',
            'rating_scores.rated_by_reference_type' => 'client',
            'rating_scores.rated_by_reference_id' => DB::raw('loyalty_cards.client_uuid'),
        ])->whereBetween('rating_scores.created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ]);
    }
}
