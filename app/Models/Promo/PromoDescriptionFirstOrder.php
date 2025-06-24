<?php

namespace App\Models\Promo;

use App\Models\PromoDescription;

/**
 * Same model for another discount type (we need Eloquent's models to make it work properly)
 */
class PromoDescriptionFirstOrder extends PromoDescription
{
    protected $table = 'promo_descriptions';

    const UUID = '0c068a20-0b73-4659-ace2-d59816bba4e9';

    public function save(array $options = [])
    {
        throw new \Exception('Cannot create or update read-only model');
    }

    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('Cannot update read-only model');
    }

    public function delete()
    {
        throw new \Exception('Cannot delete read-only model');
    }
}