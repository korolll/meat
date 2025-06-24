<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssortmentPropertyDataTypeResource;
use App\Models\AssortmentPropertyDataType;

class AssortmentPropertyDataTypeController extends Controller
{
    /**
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', AssortmentPropertyDataType::class);

        return AssortmentPropertyDataTypeResource::collection(
            AssortmentPropertyDataType::all()
        );
    }

    /**
     * @param AssortmentPropertyDataType $assortmentPropertyDataType
     * @return AssortmentPropertyDataTypeResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AssortmentPropertyDataType $assortmentPropertyDataType)
    {
        $this->authorize('view', $assortmentPropertyDataType);

        return AssortmentPropertyDataTypeResource::make($assortmentPropertyDataType);
    }
}
