<?php


namespace App\Services\Management\Profiles\Promotions;


use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\Client;
use App\Models\PromotionInTheShopLastPurchase;
use App\Models\User;
use App\Services\Database\Table\DiscountForbiddenCatalogRecursiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class InTheShopAssortmentFinder implements InTheShopAssortmentFinderContract
{
    /**
     * @var array
     */
    private array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param \App\Models\User $shop
     * @param int              $finedQuantity
     *
     * @return \Illuminate\Support\Enumerable
     */
    public function findAssortmentMarkedNew(User $shop, int $limit = 2): Enumerable
    {
        $assortmentPropertyUuid = $this->conf('assortment_property_uuid');
        $mark = $this->conf('property_new');
        return $this->getAssortmentPropertyUuids($shop, $assortmentPropertyUuid, $mark, $limit);
    }

    /**
     * @param \App\Models\User $shop
     * @param int              $finedQuantity
     *
     * @return \Illuminate\Support\Enumerable
     */
    public function findAssortmentMarkedSale(User $shop, int $finedQuantity = 6): Enumerable
    {
        $assortmentPropertyUuid = $this->conf('assortment_property_uuid');
        $mark = $this->conf('property_sale');
        return $this->getAssortmentPropertyUuids($shop, $assortmentPropertyUuid, $mark);
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\User   $shop
     * @param int                $finedQuantity
     *
     * @return \Illuminate\Support\Enumerable
     */
    public function findAssortmentNotBoughtLongTime(Client $client, User $shop, int $finedQuantity = 2): Enumerable
    {
        $offerDelayInterval = $this->conf('offer_delay');
        $moment = Date::today()->subDays($offerDelayInterval);

        $bannedCatalogsTable = new DiscountForbiddenCatalogRecursiveTable();
        $bannedQuery = $bannedCatalogsTable->table('banned_catalogs');

        $query = PromotionInTheShopLastPurchase::where('client_uuid', $client->uuid)
            ->whereDate('promotion_in_the_shop_last_purchases.updated_at', '<', $moment)
            ->joinSub($bannedQuery, 'bc', 'bc.catalog_uuid', '=', 'promotion_in_the_shop_last_purchases.catalog_uuid', 'left')
            ->whereNull('bc.catalog_uuid')
            ->join('assortments', 'assortments.catalog_uuid', 'promotion_in_the_shop_last_purchases.catalog_uuid')
            ->leftJoin('discount_forbidden_assortments', 'discount_forbidden_assortments.assortment_uuid', '=', 'assortments.uuid')
            ->whereNull('discount_forbidden_assortments.assortment_uuid')
            ->inRandomOrder()
            ->limit($finedQuantity)
            ->toBase();

        return $this->applyProductFilter($shop, $query)
            ->get('products.assortment_uuid as uuid');
    }

    /**
     * @param \App\Models\User $shop
     * @param string           $propertyUuid
     * @param string           $value
     * @param int              $limit
     *
     * @return \Illuminate\Support\Enumerable
     */
    protected function getAssortmentPropertyUuids(User $shop, string $propertyUuid, string $value, int $limit = 6): Enumerable
    {
        $query = DB::table('assortment_assortment_property')
            ->where('assortment_property_uuid', $propertyUuid)
            ->where('value', $value)
            ->inRandomOrder()
            ->limit($limit);

        return $this->applyProductFilter($shop, $query, 'assortment_assortment_property', 'assortment_uuid')
            ->get('assortment_assortment_property.assortment_uuid as uuid');
    }

    /**
     * @param \App\Models\User                   $shop
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $table
     * @param string                             $fk
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyProductFilter(User $shop, \Illuminate\Database\Query\Builder $query, string $table = 'assortments', string $fk = 'uuid')
    {
        return $query->join('products', function (JoinClause $join) use ($shop, $table, $fk) {
            $join->on('products.assortment_uuid', '=', "$table.$fk");
            $join->where('products.user_uuid', $shop->uuid);
            $join->where('products.quantity', '>', 0);
            $join->where('products.price', '>', 0);
        });
    }

    /**
     * @param string $field
     * @param null   $default
     *
     * @return array|\ArrayAccess|mixed
     */
    protected function conf(string $field, $default = null)
    {
        return Arr::get($this->config, $field, $default);
    }
}
