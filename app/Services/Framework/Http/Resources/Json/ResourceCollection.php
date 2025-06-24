<?php

namespace App\Services\Framework\Http\Resources\Json;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

abstract class ResourceCollection extends BaseResourceCollection
{
    /**
     * @param mixed $resource
     */
    public function __construct($resource)
    {
        if ($resource instanceof Model || $resource instanceof Collection || $resource instanceof AbstractPaginator) {
            static::loadMissing($resource);
        }

        parent::__construct($resource);
    }

    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        //
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $collection = $this->collection->map(function ($resource) {
            return $this->resource($resource);
        });

        return $collection->all();
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

    /**
     * @param mixed $resource
     * @return array
     */
    abstract public function resource($resource);
}
