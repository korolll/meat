<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\VacancyStoreRequest;
use App\Http\Resources\VacancyResource;
use App\Http\Responses\VacancyCollectionResponse;
use App\Models\Vacancy;
use Illuminate\Http\Response;

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

    /**
     * @param VacancyStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(VacancyStoreRequest $request)
    {
        $this->authorize('create', Vacancy::class);

        $vacancy = new Vacancy($request->validated());
        $vacancy->saveOrFail();

        return VacancyResource::make($vacancy);
    }

    /**
     * @param Vacancy $vacancy
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Vacancy $vacancy)
    {
        $this->authorize('view', $vacancy);

        return VacancyResource::make($vacancy);
    }

    /**
     * @param VacancyStoreRequest $request
     * @param Vacancy $vacancy
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(VacancyStoreRequest $request, Vacancy $vacancy)
    {
        $this->authorize('update', $vacancy);

        $vacancy->fill($request->validated());
        $vacancy->saveOrFail();

        return VacancyResource::make($vacancy);
    }

    /**
     * @param \App\Models\Vacancy $vacancy
     *
     * @return \App\Http\Resources\VacancyResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Vacancy $vacancy)
    {
        $this->authorize('delete', $vacancy);
        $vacancy->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
