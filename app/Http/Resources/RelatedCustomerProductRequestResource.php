<?php

namespace App\Http\Resources;

use App\Models\ProductRequest;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelatedCustomerProductRequestResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'customerUser' => function (Relation $query) {
                return $query->select('uuid', 'organization_name');
            },
        ]);
    }

    /**
     * @param CustomerProductRequest $productRequest
     * @return array
     */
    public function resource($productRequest)
    {
        return [
            'uuid' => $productRequest->uuid,
            'organisation_name' => $productRequest->customerUser->organization_name,
        ];
    }
}
