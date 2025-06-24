<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LaboratoryTestStatusResource;
use App\Models\LaboratoryTestStatus;

class LaboratoryTestStatusController extends Controller
{
    /**
     * @return mixed
     * @throws \Throwable
     */
    public function index()
    {
        return LaboratoryTestStatusResource::collection(LaboratoryTestStatus::all());
    }

    /**
     * @param LaboratoryTestStatus $laboratoryTestStatus
     * @return mixed
     */
    public function show(LaboratoryTestStatus $laboratoryTestStatus)
    {
        return LaboratoryTestStatusResource::make($laboratoryTestStatus);
    }
}
