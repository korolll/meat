<?php

namespace App\Services\Framework\Http\Resources\Json;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;
use Illuminate\Pagination\AbstractPaginator;

abstract class JsonResource extends BaseJsonResource
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
     * @return AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return new AnonymousResourceCollection($resource, get_called_class());
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
        return $this->resource($this->resource);
    }

    /**
     * @param mixed $resource
     * @return array
     */
    abstract public function resource($resource);
}
