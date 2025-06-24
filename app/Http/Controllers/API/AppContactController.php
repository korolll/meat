<?php

namespace App\Http\Controllers\API;

use App\Models\AppContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests\AppContactUpdateRequest;


class AppContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $contacts = AppContact::all()->first()->toArray();
        return response()->json(['data'=>$contacts],200);
    }

    /**
     * @param VacancyStoreRequest $request
     * @param Vacancy $vacancy
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(AppContactUpdateRequest $request)
    {
        //$this->authorize('update', $user);

        $contacts = AppContact::all()->first();
        $contacts->fill($request->validated());
        $contacts->saveOrFail();

        return response()->json(['data'=>$contacts],200);
    }
}
