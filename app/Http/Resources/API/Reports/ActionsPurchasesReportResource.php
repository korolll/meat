<?php

namespace App\Http\Resources\API\Reports;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;

class ActionsPurchasesReportResource extends JsonResource
{
    /**
     * @var \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected PromoDescriptionResolverInterface $descriptionResolver;

    /**
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->descriptionResolver = app(PromoDescriptionResolverInterface::class);
    }

    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource, string $prefix = '')
    {

    }

    /**
     * @param \StdClass $row
     *
     * @return array
     */
    public function resource($row)
    {
        $info = $this->descriptionResolver->resolve($row->discountable_type);
        if ($info) {
            $row->discount_type_color = $info->color;
            $row->discount_type_name = $info->name;
        }

        return [
            'discountable_type' => $row->discountable_type,
            'discount_type_color' => $row->discount_type_color,
            'discount_type_name' => $row->discount_type_name,
            'total_sum' => (int) $row->total_sum,
            'total_quantity' => (int) $row->total_quantity,
        ];
    }
}
