<?php

namespace App\Services\Models\Assortment;

use App\Models\Assortment;

class BannedAssortmentChecker implements BannedAssortmentCheckerInterface
{
    protected array $cachedData = [];

    /**
     * @param iterable<Assortment> $assortments
     * @param bool     $useCache
     *
     * @return array<string, bool>
     */
    public function checkCollection(iterable $assortments, bool $useCache = true): array
    {
        if (!$useCache) {
            return $this->checkBanned($assortments);
        }

        $result = [];
        $filtered = [];
        foreach ($assortments as $assortment) {
            $uuid = $assortment->uuid;
            if (isset($this->cachedData[$uuid])) {
                $result[$uuid] = $this->cachedData[$uuid];
            } else {
                $filtered[] = $assortment;
            }
        }

        if ($filtered) {
            $result += $this->checkBanned($filtered);
            $this->cachedData += $result;
        }

        return $result;
    }

    protected function checkBanned(iterable $assortments): array
    {
        return Assortment::isForbiddenForDiscountList($assortments);
    }

    public function clearCache(): void
    {
        $this->cachedData = [];
    }
}
