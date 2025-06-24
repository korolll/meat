<?php

namespace App\Http\Resources;

use App\Models\PaymentVendorSetting;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class PaymentVendorSettingResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'users' => function (Relation $query) {
                return $query->select('uuid');
            },
        ]);
    }

    /**
     * @param PaymentVendorSetting $resource
     * @return array
     */
    public function resource($resource)
    {
        $users = $resource->users;
        $stores = [];
        foreach ($users as $user) {
            $stores[] = [
                'store_uuid' => $user->uuid,
                'is_active' => $user->pivot->is_active,
            ];
        }

        return [
            'uuid' => $resource->uuid,
            'payment_vendor_id' => $resource->payment_vendor_id,
            'config' => $resource->config,
            'stores' => $stores,
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
