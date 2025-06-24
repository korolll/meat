<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class UserResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'userAdditionalEmails' => function (Relation $query) {
                return $query->select('uuid', 'user_uuid', 'email');
            },
            'files' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            }
        ]);

        if ($resource->is_store) {
            $resource->loadMissing([
                'image' => function (Relation $query) {
                    return $query->select('*');
                }
            ]);
        }
    }

    /**
     * @param User $user
     * @return array
     */
    public function resource($user)
    {
        $zone = $user->deliveryZone()->first();
        $resource = [
            'uuid' => $user->uuid,
            'user_type_id' => $user->user_type_id,
            'full_name' => $user->full_name,
            'legal_form_id' => $user->legal_form_id,
            'organization_name' => $user->organization_name,
            'organization_address' => $user->organization_address,
            'address' => $user->address,
            'email' => $user->email,
            'phone' => $user->phone,
            'inn' => $user->inn,
            'kpp' => $user->kpp,
            'ogrn' => $user->ogrn,
            'region_uuid' => $user->region_uuid,
            'position' => $user->position,
            'bank_correspondent_account' => $user->bank_correspondent_account,
            'bank_current_account' => $user->bank_current_account,
            'bank_identification_code' => $user->bank_identification_code,
            'bank_name' => $user->bank_name,
            'user_verify_status_id' => $user->user_verify_status_id,
            'is_email_verified' => $user->is_email_verified,
            'created_at' => $user->created_at,
            'additional_emails' => $user->userAdditionalEmails->pluck('email'),
            'files' => FileShortInfoResource::collection($user->files),

            $this->mergeWhen($user->is_store, [
                'brand_name' => $user->brand_name,
                'work_hours_from' => $user->work_hours_from,
                'work_hours_till' => $user->work_hours_till,
                'address_latitude' => $user->address_latitude,
                'address_longitude' => $user->address_longitude,

                'signer_type_id' => $user->signer_type_id,
                'signer_full_name' => $user->signer_full_name,
                'power_of_attorney_number' => $user->power_of_attorney_number,
                'date_of_power_of_attorney' => $user->date_of_power_of_attorney,
                'ip_registration_certificate_number' => $user->ip_registration_certificate_number,
                'date_of_ip_registration_certificate' => $user->date_of_ip_registration_certificate,

                'has_parking' => $user->has_parking,
                'has_ready_meals' => $user->has_ready_meals,
                'has_atms' => $user->has_atms,
                'image' => FileShortInfoResource::make($user->image),
                'allow_find_nearby' => $user->allow_find_nearby,

                'less_zone_distance' => $zone?->less_zone_distance,
                'between_zone_distance' => $zone?->between_zone_distance,
                'more_zone_distance' => $zone?->more_zone_distance,
                'max_zone_distance' => $zone?->max_zone_distance,
                'less_zone_price' => $zone?->less_zone_price,
                'between_zone_price' => $zone?->between_zone_price,
                'more_zone_price' => $zone?->more_zone_price,
            ]),
        ];

        return $resource;
    }
}
