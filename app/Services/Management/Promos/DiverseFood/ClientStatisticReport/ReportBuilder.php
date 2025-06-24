<?php

namespace App\Services\Management\Promos\DiverseFood\ClientStatisticReport;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ReceiptLine;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Iterator;
use IteratorAggregate;


class ReportBuilder implements IteratorAggregate
{
    private CarbonInterface $start;
    private CarbonInterface $end;

    private ?int $minRatings = null;
    private ?int $minPurchases = null;

    private Enumerable $data;

    private ?string $excludedCatalogUuid;

    public function __construct(CarbonInterface $start, CarbonInterface $end)
    {
        $this->start = $start->copy();
        $this->end = $end->copy();

        $this->data = collect();

        $this->excludedCatalogUuid = (string) config('app.catalogs.promo.diverse_food.excluded_uuid') ?: null;
    }

    public function setMinPurchases(int $v): ReportBuilder
    {
        $this->minPurchases = $v;
        return $this;
    }

    public function setMinRatings(int $v): ReportBuilder
    {
        $this->minRatings = $v;
        return $this;
    }


    public function build(): void
    {
        $this->data = $this->makeQuery()->get();
    }

    public function makeQuery(): Builder
    {
        $query = DB::table('receipts');
        $query->join('loyalty_cards', 'loyalty_cards.uuid', '=', 'receipts.loyalty_card_uuid');
        $query->join('receipt_lines', function (JoinClause $join) {
            $join->on('receipt_lines.receipt_uuid', '=', 'receipts.uuid');

            if ($this->excludedCatalogUuid) {
                $raw = "receipt_lines.assortment_uuid NOT IN (
                    SELECT assortments.uuid FROM catalog_with_all_children('{$this->excludedCatalogUuid}') as c
                    JOIN assortments ON c.uuid = assortments.catalog_uuid
                )";
                $join->whereRaw($raw);
            }
        });
        $query->leftJoin('rating_scores', function(JoinClause $join) {
            $join->on('rating_scores.rated_reference_id', '=', 'receipt_lines.assortment_uuid');
            $join->on('rating_scores.rated_by_reference_id', '=', 'loyalty_cards.client_uuid');
            $join->on('rating_scores.rated_through_reference_id', '=', 'receipt_lines.uuid');
            $join->where('rating_scores.rated_reference_type', Assortment::MORPH_TYPE_ALIAS);
            $join->where('rating_scores.rated_by_reference_type', Client::MORPH_TYPE_ALIAS);
            $join->where('rating_scores.rated_through_reference_type', ReceiptLine::MORPH_TYPE_ALIAS);

            $join->whereBetween('rating_scores.created_at', [$this->start, $this->end]);
        });
        $query->whereBetween('receipts.created_at', [$this->start, $this->end]);
        $query->groupBy('loyalty_cards.client_uuid');
        if (!is_null($this->minPurchases)) {
            $query->havingRaw('count(distinct receipt_lines.assortment_uuid) >= ?', [$this->minPurchases]);
        }
        if (!is_null($this->minRatings)) {
            $query->havingRaw('count(distinct rating_scores.rated_reference_id) >= ?', [$this->minRatings]);
        }
        $query->select(['loyalty_cards.client_uuid']);
        $query->selectRaw('count(distinct receipt_lines.assortment_uuid) as count_purchases');
        $query->selectRaw('count(distinct rating_scores.rated_reference_id) as count_rating_scores');

        return $query;
    }

    /**
     * @return Iterator|ReportRow[]
     */
    public function getIterator(): Iterator
    {
        foreach ($this->data as $row) {
            yield new ReportRow((array) $row);
        }
    }
}
