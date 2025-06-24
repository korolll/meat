<?php

namespace App\Http\Responses;

use App\Http\Resources\PurchaseLineResource;
use App\Models\PurchaseView;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class PurchaseLineResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PurchaseLineResource::class;

    /**
     * @var string
     */
    protected $model = PurchaseView::class;

    /**
     * @var array
     */
    protected $attributes = [
        'source',
        'source_id',
        'source_line',
        'source_line_id',
        'client_uuid',
        'product_uuid',
        'created_at',
        'is_rated',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'is_rated',
    ];

    /**
     * @param string $operator
     * @param        $value
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function whereIsRated(string $operator, $value)
    {
        $this->query->leftJoin('rating_scores', function (JoinClause $join) {
            $join->where('rating_scores.rated_reference_type', 'assortment');
            $join->on('rating_scores.rated_reference_id', 'products.assortment_uuid');
            $join->where('rating_scores.rated_by_reference_type', 'client');
            $join->on('rating_scores.rated_by_reference_id', 'purchases_view.client_uuid');
            $join->on('rating_scores.rated_through_reference_type', 'purchases_view.source_line');
            $join->on('rating_scores.rated_through_reference_id', 'purchases_view.source_line_id');
        });

        if ($value) {
            $this->query->whereNotNull('rating_scores.uuid');
            $this->query->distinct(['rating_scores.rated_reference_id']);
            $this->query->orderBy('rating_scores.rated_reference_id');
            $this->query->orderBy('rating_scores.uuid');
        } else {
            $this->query->whereNull('rating_scores.uuid');
        }

        return $this->query;
    }
}
