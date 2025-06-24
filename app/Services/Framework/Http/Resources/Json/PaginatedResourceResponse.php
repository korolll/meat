<?php

namespace App\Services\Framework\Http\Resources\Json;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse as BasePaginatedResourceResponse;
use Illuminate\Support\Arr;

class PaginatedResourceResponse extends BasePaginatedResourceResponse
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function paginationInformation($request)
    {
        $paginated = $this->resource->resource->toArray();

        return [
            'meta' => $this->meta($paginated),
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param  array $paginated
     * @return array
     */
    protected function meta($paginated)
    {
        return Arr::only($paginated, [
            'current_page',
            'from',
            'last_page',
            'per_page',
            'to',
            'total',
        ]);
    }
}
