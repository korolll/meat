<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\AssortmentUnitCollectionResponse;
use App\Models\AssortmentUnit;

class AssortmentUnitController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', AssortmentUnit::class);

        return AssortmentUnitCollectionResponse::create(
            AssortmentUnit::query()
        );
    }
}
