<?php

namespace App\Http\Resources\Clients\API;

use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Resources\FileShortInfoResource;

class ProfileResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'selectedStore' => function (Relation $query) {
                return $query->select('uuid', 'address', 'image_uuid');
            }
        ]);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function resource($client)
    {
        $transactionExist = $client->clientBonusTransactions()
            ->where('reason', ClientBonusTransaction::REASON_PROFILE_FILLED)
            ->exists();

        return [
            'uuid' => $client->uuid,
            'phone' => $client->phone,
            'name' => $client->name,
            'email' => $client->email,
            'sex' => $client->sex,
            'birth_date' => $client->birth_date,
            'created_at' => $client->created_at,
            'consent_to_service_newsletter' => $client->consent_to_service_newsletter,
            'consent_to_receive_promotional_mailings' => $client->consent_to_receive_promotional_mailings,
            'is_agree_with_diverse_food_promo' => $client->is_agree_with_diverse_food_promo,
            'selected_store_user_uuid' => $client->selected_store_user_uuid,
            'selected_store_address' => optional($client->selectedStore)->address,
            'image' => FileShortInfoResource::make(optional($client->selectedStore)->image),
            'bonus_balance' => $client->bonus_balance,
            'app_version' => $client->app_version,
            'filled_profile_bonuses_added' => $transactionExist,
            'mark_deleted_at' => $client->mark_deleted_at,
        ];
    }
}
