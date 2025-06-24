<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Models\LaboratoryTest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class LaboratoryTestResource extends JsonResource
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
            'customerFiles' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
            'executorFiles' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
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

        $base = $laboratoryTest->only([
            'uuid',
            'laboratory_test_appeal_type_uuid',
            'laboratory_test_status_id',

            'customer_full_name',
            'customer_organization_name',
            'customer_organization_address',
            'customer_inn',
            'customer_kpp',
            'customer_ogrn',

            'customer_position',
            'customer_bank_correspondent_account',
            'customer_bank_current_account',
            'customer_bank_identification_code',
            'customer_bank_name',

            'batch_number',
            'parameters',

            'assortment_barcode',
            'assortment_uuid',
            'assortment_name',
            'assortment_manufacturer',
            'assortment_production_standard_id',
            'assortment_supplier_user_uuid',
        ]);

        $adds = [
            'executor_user_uuid' => $executor->uuid,
            'executor_organization_name' => $executor->organization_name,

            'customer_files' => FileShortInfoResource::collection($laboratoryTest->customerFiles),
            'executor_files' => FileShortInfoResource::collection($laboratoryTest->executorFiles),
        ];

        return array_merge($base, $adds);
    }
}
