<?php

namespace App\Http\Resources;

use App\Models\LaboratoryTest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class LaboratoryTestCollectionResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'executorUser' => function (Relation $query) {
                return $query->select('uuid', 'organization_name');
            },
        ]);
    }

    /**
     * @param LaboratoryTest $laboratoryTest
     * @return array
     */
    public function resource($laboratoryTest)
    {
        $executor = optional($laboratoryTest->executorUser);
        return [
            'uuid' => $laboratoryTest->uuid,
            'customer_user_uuid' => $laboratoryTest->customer_user_uuid,
            'customer_full_name' => $laboratoryTest->customer_full_name,
            'executor_user_uuid' => $executor->uuid,
            'executor_organization_name' => $executor->organization_name,
            'laboratory_test_status_id' => $laboratoryTest->laboratory_test_status_id,
            'created_at' => $laboratoryTest->created_at,
        ];
    }
}
