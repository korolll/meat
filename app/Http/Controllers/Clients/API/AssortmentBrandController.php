<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\AssortmentBrandResource;
use App\Http\Responses\Clients\API\AssortmentBrandCollectionResponse;
use App\Models\AssortmentBrand;

class AssortmentBrandController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', AssortmentBrand::class);

        return AssortmentBrandCollectionResponse::create(
            AssortmentBrand::query()
        );
    }
}
