<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\PromoDescriptionCollectionResponse;
use App\Models\PromoDescription;

class PromoDescriptionController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', PromoDescription::class);

        return PromoDescriptionCollectionResponse::create(
            PromoDescription::query()
        );
    }
}
