<?php


namespace App\Services\Models\Catalog;


class CatalogMap implements CatalogMapInterface
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * CatalogMap constructor.
     *
     * @param array<\App\Models\Catalog> $catalogs
     */
    public function __construct(array $catalogs)
    {
        $this->makeMap($catalogs);
    }

    /**
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @param array<\App\Models\Catalog> $catalogs
     */
    protected function makeMap(array $catalogs): void
    {
        $map = [];
        $rootIds = [];

        foreach ($catalogs as $catalog) {
            $uuid = $catalog->uuid;
            $parentUuid = $catalog->catalog_uuid;
            if (! $parentUuid) {
                $rootIds[] = $catalog->uuid;
            } else {
                if (! isset($map[$parentUuid])) {
                    $map[$parentUuid] = [];
                }

                $map[$parentUuid][] = $uuid;
            }
        }

        $this->map = [];
        $this->applyRoots($rootIds, $this->map, $map);
    }

    /**
     * @param array $roots
     * @param array $arr
     * @param array $map
     */
    protected function applyRoots(array $roots, array &$arr, array $map): void
    {
        foreach ($roots as $rootId) {
            $arr[$rootId] = [];
            if (isset($map[$rootId])) {
                $this->applyRoots($map[$rootId], $arr[$rootId], $map);
            }
        }
    }
}
