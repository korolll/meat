<?php

namespace App\Services\Models\User;

use App\Models\AssortmentVerifyStatus;
use App\Models\User;
use App\Services\Models\Catalog\CatalogMapInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductsInCatalogCacher implements ProductsInCatalogCacherInterface
{
    /**
     * @param \App\Models\User                                 $user
     * @param \App\Services\Models\Catalog\CatalogMapInterface $map
     */
    public function cache(User $user, CatalogMapInterface $map): void
    {
        $aggregated = $this->loadCatalogCounts($user);
        $toInsert = $this->countProducts($aggregated, $map);

        /** @var \Illuminate\Database\Eloquent\Collection $chunk */
        foreach ($toInsert->chunk(500) as $chunk) {
            $this->cacheData($user, $chunk);
        }
    }

    /**
     * @param \Illuminate\Support\Collection                   $aggregated
     * @param \App\Services\Models\Catalog\CatalogMapInterface $map
     *
     * @return \Illuminate\Support\Collection
     */
    protected function countProducts(Collection $aggregated, CatalogMapInterface $map): Collection
    {
        $map = $map->getMap();
        $result = collect();
        $this->aggregateValues($result, $aggregated, '0', $map);
        $result->offsetUnset('0');
        return $result;
    }

    /**
     * @param \Illuminate\Support\Collection $result
     * @param \Illuminate\Support\Collection $aggregated
     * @param                                $parentId
     * @param                                $nodes
     *
     * @return array
     */
    protected function aggregateValues(Collection $result, Collection $aggregated, $parentId, $nodes)
    {
        $sum = 0;
        $properties = [];
        $tags = [];
        if ($aggregated->has($parentId)) {
            $data = $aggregated->get($parentId);
            $sum += $data['count'];
            $properties += $data['properties'];
            $tags += $data['tags'];
        }

        foreach ($nodes as $newParentId => $childs) {
            $data = $this->aggregateValues($result, $aggregated, $newParentId, $childs);
            $sum += $data['count'];
            $properties += $data['properties'];
            $tags += $data['tags'];
        }

        $resData = [
            'count' => $sum,
            'properties' => $properties,
            'tags' => $tags
        ];
        $result[$parentId] = $resData;
        return $resData;
    }


    /**
     * @param \App\Models\User               $user
     * @param \Illuminate\Support\Collection $chunk
     *
     * @return bool
     */
    protected function cacheData(User $user, Collection $chunk): bool
    {
        $str = '';
        $isFirst = true;
        $symbol = '';
        $bindings = [];
        foreach ($chunk as $catalogUuid => $data) {
            $str .= $symbol . '(?, ?, ?, ?, ?)';
            if ($isFirst) {
                $isFirst = false;
                $symbol = ',';
            }

            $bindings[] = $user->uuid;
            $bindings[] = $catalogUuid;
            $bindings[] = $data['count'];

            $props = null;
            if ($data['properties']) {
                $props = json_encode(array_keys($data['properties']));
            }
            $bindings[] = $props;

            $tags = null;
            if ($data['tags']) {
                $tags = json_encode(array_keys($data['tags']));
            }
            $bindings[] = $tags;
        }

        $query = "
            INSERT INTO user_catalog_product_counts (user_uuid, catalog_uuid, product_count, properties, tags) 
            VALUES $str
            ON CONFLICT (user_uuid, catalog_uuid) DO UPDATE 
            SET product_count = excluded.product_count,
                properties = excluded.properties,
                tags = excluded.tags
        ";

        return DB::insert($query, $bindings);
    }

    /**
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Support\Collection
     */
    protected function loadCatalogCounts(User $user)
    {
        return $user->products()
            ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
            ->leftJoin('assortment_assortment_property', 'assortment_assortment_property.assortment_uuid', '=', 'assortments.uuid')
            ->leftJoin('assortment_tag', 'assortment_tag.assortment_uuid', '=', 'assortments.uuid')
            ->where('assortments.assortment_verify_status_id', '=', AssortmentVerifyStatus::ID_APPROVED)
            ->where('products.quantity', '>', 0)
            ->whereNull('assortments.deleted_at')
            ->whereNotNull('products.price')
            ->groupBy(['assortments.catalog_uuid'])
            ->select([
                'assortments.catalog_uuid',
                DB::raw('COUNT(*) as count'),
                DB::raw("ARRAY_TO_STRING(ARRAY_AGG(DISTINCT assortment_assortment_property.assortment_property_uuid), ',') as assortment_properties"),
                DB::raw("ARRAY_TO_STRING(ARRAY_AGG(DISTINCT assortment_tag.tag_uuid), ',') as assortment_tags")
            ])
            ->get()
            ->mapWithKeys(function ($item) {
                $properties = [];
                if ($item->assortment_properties) {
                    $list = explode(',', $item->assortment_properties);
                    $properties = array_flip($list);
                }

                $tags = [];
                if ($item->assortment_tags) {
                    $list = explode(',', $item->assortment_tags);
                    $tags = array_flip($list);
                }

                return [$item->catalog_uuid => [
                    'count' => $item->count,
                    'properties' => $properties,
                    'tags' => $tags,
                ]];
            })
            ->toBase();
    }
}
