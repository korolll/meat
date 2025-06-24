<?php

namespace App\Http\Controllers\API\Profile\ProductRequests;

use App\Http\Responses\ProductPreRequestCollectionResponse;
use App\Models\ProductPreRequest;
use Illuminate\Http\JsonResponse;

class ProductPreRequestController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        $this->authorize('index', ProductPreRequest::class);

        return ProductPreRequestCollectionResponse::create(
            ProductPreRequest::where(['user_uuid' => user()->uuid])
        );
    }
}
