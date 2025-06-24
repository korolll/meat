<?php

namespace App\Services\Models\User;

use App\Models\UserDeliveryZone;

class UserDeliveryZoneHelper
{
    public static function extractZoneData(array &$properties): ?array
    {
        $fields = [
            'less_zone_price',
            'between_zone_price',
            'more_zone_price',
            'less_zone_distance',
            'between_zone_distance',
            'more_zone_distance',
            'max_zone_distance',
        ];

        $zoneData = array_intersect_key($properties, array_flip($fields));

        if (empty($zoneData)) {
            return null;
        }

        // Удаляем данные зоны из $properties, чтобы они не попали в fill()
        foreach ($fields as $field) {
            if (array_key_exists($field, $properties)) {
                unset($properties[$field]);
            }
        }

        return $zoneData;
    }

    public static function findExistingZone($zoneData)
    {
        return UserDeliveryZone::where('less_zone_price', $zoneData['less_zone_price'] ?? null)
            ->where('between_zone_price', $zoneData['between_zone_price'] ?? null)
            ->where('more_zone_price', $zoneData['more_zone_price'] ?? null)
            ->where('less_zone_distance', $zoneData['less_zone_distance'] ?? null)
            ->where('between_zone_distance', $zoneData['between_zone_distance'] ?? null)
            ->where('more_zone_distance', $zoneData['more_zone_distance'] ?? null)
            ->first();
    }
}