<?php


namespace App\Services\Management\Profiles\Promotions;


use App\Models\Client;
use App\Models\User;

interface InTheShopServiceContract
{
    /**
     *  Активирует акцию для магазина
     */
    public function activate(Client $client, User $user): void;

    public function getActivated(Client $client, User $user);
}
