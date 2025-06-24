<?php

namespace App\Http\Resources\Clients\API;

use App\Http\Resources\FileShortInfoResource;
use App\Models\Tag;
use App\Models\Catalog;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class CatalogResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'parent' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'image' => function (Relation $query) {
                return $query->select('*');
            },
        ]);
    }

    /**
     * @param Catalog $catalog
     * @return array
     */
    public function resource($catalog)
    {
        $parent = optional($catalog->parent);

        return [
            'uuid' => $catalog->uuid,
            'catalog_uuid' => $parent->uuid,
            'catalog_name' => $parent->name,
            'name' => $catalog->name,
            'level' => $catalog->level,
            'assortments_count' => $catalog->assortments_count,
            'image' => FileShortInfoResource::make($catalog->image),
            'sort_number' => $catalog->sort_number,
            'is_final_level' => $this->when(isset($catalog->is_final_level), (bool) $catalog->is_final_level),

            $this->mergeWhen($catalog->assortments_count_in_store !== null, function () use ($catalog) {
                return [
                    'assortments_count_in_store' => $catalog->assortments_count_in_store,
                ];
            }),

            $this->mergeWhen($catalog->assortments_properties_in_store !== null, function () use ($catalog) {
                $list = json_decode($catalog->assortments_properties_in_store, true);
                return [
                    'assortments_properties_in_store' => $list,
                ];
            }),

            $this->mergeWhen($catalog->assortments_tags_in_store !== null, function () use ($catalog) {
                $list = json_decode($catalog->assortments_tags_in_store, true);
                $result = [];
                foreach($list as $elem) {
                    $tag = Tag::where('uuid', $elem)->get()->first();
                    $result[] = $tag->name;
                }
                return [
                    'assortments_tags_in_store' => $result,
                ];
            }),
        ];
    }
}
