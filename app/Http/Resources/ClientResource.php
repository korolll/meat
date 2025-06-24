<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ClientResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'selectedStore' => function (Relation $query) {
                return $query->select('uuid', 'address');
            }
        ]);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function resource($client)
    {
        return [
            'uuid' => $client->uuid,
            'phone' => $client->phone,
            'name' => $client->name,
            'email' => $client->email,
            'sex' => $client->sex,
            'birth_date' => $client->birth_date,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
            'last_visited_at' => $client->last_visited_at,
            'is_agree_with_diverse_food_promo' => $client->is_agree_with_diverse_food_promo,
            'consent_to_service_newsletter' => $client->consent_to_service_newsletter,
            'consent_to_receive_promotional_mailings' => $client->consent_to_receive_promotional_mailings,
            'bonus_balance' => $client->bonus_balance,
            'app_version' => $client->app_version,
        ];
    }
}
