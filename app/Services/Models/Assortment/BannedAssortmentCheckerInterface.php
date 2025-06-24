<?php

namespace App\Services\Models\Assortment;

interface BannedAssortmentCheckerInterface
{
    /**
     * Check banned assortments
     * Returns MAP <assortment->uuid, bool>
     *
     * @param iterable $assortments
     * @param bool     $useCache
     *
     * @return array<string, bool>
     */
    public function checkCollection(iterable $assortments, bool $useCache = true): array;

    public function clearCache(): void;
}
