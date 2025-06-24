<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SignerTypeResource;
use App\Models\SignerType;

class SignerTypeController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        return SignerTypeResource::collection(
            SignerType::all()
        );
    }
}
