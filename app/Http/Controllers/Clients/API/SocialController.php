<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialStoreRequest;
use App\Http\Resources\SocialResource;
use App\Http\Responses\SocialCollectionResponse;
use App\Models\Social;

class SocialController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
//        $this->authorize('index', Onboarding::class);

        return SocialCollectionResponse::create(
            Social::query()
        );
    }
}
