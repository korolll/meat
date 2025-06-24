<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaboratoryTestAppealTypeStoreRequest;
use App\Http\Resources\LaboratoryTestAppealTypeResource;
use App\Http\Responses\LaboratoryTestAppealTypeCollectionResponse;
use App\Models\LaboratoryTestAppealType;

class LaboratoryTestAppealTypeController extends Controller
{
    /**
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function index()
    {
        $this->authorize('index', LaboratoryTestAppealType::class);

        return LaboratoryTestAppealTypeCollectionResponse::create(
            LaboratoryTestAppealType::query()
        );
    }

    /**
     * @param LaboratoryTestAppealTypeStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(LaboratoryTestAppealTypeStoreRequest $request)
    {
        $this->authorize('create', LaboratoryTestAppealType::class);

        $laboratoryTestAppealType = new LaboratoryTestAppealType($request->validated());
        $laboratoryTestAppealType->saveOrFail();

        return LaboratoryTestAppealTypeResource::make($laboratoryTestAppealType);
    }

    /**
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        $this->authorize('view', $laboratoryTestAppealType);

        return LaboratoryTestAppealTypeResource::make($laboratoryTestAppealType);
    }

    /**
     * @param LaboratoryTestAppealTypeStoreRequest $request
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(LaboratoryTestAppealTypeStoreRequest $request, LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        $this->authorize('update', $laboratoryTestAppealType);

        $laboratoryTestAppealType->fill($request->validated());
        $laboratoryTestAppealType->saveOrFail();

        return LaboratoryTestAppealTypeResource::make($laboratoryTestAppealType);
    }

    /**
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        $this->authorize('delete', $laboratoryTestAppealType);

        $laboratoryTestAppealType->delete();

        return LaboratoryTestAppealTypeResource::make($laboratoryTestAppealType);
    }
}
