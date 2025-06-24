<?php

namespace App\Services\Framework\Http\Resources\Json;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as BaseAnonymousResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class AnonymousResourceCollection extends BaseAnonymousResourceCollection
{
    /**
     * @param mixed $resource
     * @param string $collects
     */
    public function __construct($resource, $collects)
    {
        if ($resource instanceof Model || $resource instanceof Collection || $resource instanceof AbstractPaginator) {
            call_user_func([$collects, 'loadMissing'], $resource);
        }

        parent::__construct($resource, $collects);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return $this->resource instanceof AbstractPaginator
            ? (new PaginatedResourceResponse($this))->toResponse($request)
            : parent::toResponse($request);
    }
}
