<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentProductResource;
use App\Models\Product;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\ProductRequestSupplierStatus;
use App\Models\Rating;
use App\Models\RatingType;
use App\Models\User;
use App\Services\Framework\Http\CollectionRequest;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AssortmentProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentProductResource::class;

    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * @var array
     */
    protected $attributes = [
        'user_uuid',
        'user_organization_name',
        'user_supplier_rating',
        'user_done_supplier_product_requests_count',
        'quantum',
        'min_quantum_in_order',
        'price',
        'user_supplier_product_requests_today',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'user_organization_name' => 'user.organization_name',
        'user_uuid' => 'user.uuid',
    ];

    /**
     * @var array
     */
    protected $virtualColumns = [
        'user_supplier_rating',
        'user_done_supplier_product_requests_count',
    ];

    /**
     * @param CollectionRequest $request
     * @param Builder $query
     * @throws \App\Exceptions\TealsyException
     */
    public function __construct(CollectionRequest $request, $query)
    {
        parent::__construct($request, $query);

        // @todo Явно требуется отказаться от текущей реализации EloquentCollectionResponse
        $query->select($query->getModel()->qualifyColumn('*'));

        // Подзапрос рейтинга
        $subQuery1 = Rating::select('value')->where([
            'reference_type' => User::newModelInstance()->getMorphClass(),
            'reference_id' => DB::raw('products.user_uuid'),
            'rating_type_id' => RatingType::ID_SUPPLIER,
        ]);

        // Подзапрос кол-ва выполненных заявок на поставку
        $subQuery2 = SupplierProductRequest::selectRaw('COUNT(uuid)')->where([
            'supplier_user_uuid' => DB::raw('products.user_uuid'),
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_DONE,
        ]);
        $userUuid = \user()->uuid;
        $subQuery3 = "exists(
           select *
           from product_pre_request_customer_supplier_relation ppr
           where ppr.supplier_user_uuid = products.user_uuid
             and ppr.customer_user_uuid = '$userUuid'
           )";

        // Добавляем колонки к запросу
        $query->selectSub($subQuery1, 'user_supplier_rating');
        $query->selectSub($subQuery2, 'user_done_supplier_product_requests_count');
        $query->selectSub($subQuery3, 'user_supplier_product_requests_today');
    }
}
