<?php


namespace App\Services\Management\Profiles\Promotions;


use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Enumerable;

interface InTheShopAssortmentFinderContract
{
    public function findAssortmentMarkedNew(User $shop, int $finedQuantity): Enumerable;
    public function findAssortmentMarkedSale(User $shop, int $finedQuantity): Enumerable;
    public function findAssortmentNotBoughtLongTime(Client $client, User $shop, int $finedQuantity): Enumerable;
}
