<?php

namespace App\Services\Storaging\Catalog;

use App\Models\Catalog;
use App\Models\User;
use App\Services\Storaging\Catalog\Contracts\DefaultCatalogFinderContract;
use Illuminate\Database\Eloquent\Model;

class DefaultCatalogFinder implements DefaultCatalogFinderContract
{
    /**
     * @var string
     */
    protected $defaultCatalogName;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->defaultCatalogName = config('app.catalogs.default_catalog_name');
    }

    /**
     * @param User $owner
     * @return Catalog|Model
     */
    public function find(User $owner)
    {
        $attributes = [
            'name' => $this->defaultCatalogName,
        ];

        $values = [
            'user_uuid' => $owner->uuid,
        ];

        return $owner->catalogs()->firstOrCreate($attributes, $values);
    }
}
