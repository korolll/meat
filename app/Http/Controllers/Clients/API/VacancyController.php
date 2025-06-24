<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\VacancyStoreRequest;
use App\Http\Resources\VacancyResource;
use App\Http\Responses\VacancyCollectionResponse;
use App\Models\Vacancy;

class VacancyController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
//        $this->authorize('index', Vacancy::class);

        return VacancyCollectionResponse::create(
            Vacancy::query()
        );
    }
}
