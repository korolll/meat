<?php

namespace App\Http\Controllers\API;

use App\Contracts\Management\LaboratoryTest\StatusTransitionManagerContract;
use App\Http\Controllers\Controller;
use App\Http\Resources\LaboratoryTestResource;
use App\Http\Responses\LaboratoryTestCollectionResponse;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestStatus;
use Illuminate\Http\Response;

class LaboratoryTestController extends Controller
{
    /**
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function index()
    {
        $this->authorize('index', LaboratoryTest::class);

        return LaboratoryTestCollectionResponse::create(
            LaboratoryTest::new()
        );
    }

    /**
     * @param LaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LaboratoryTest $laboratoryTest)
    {
        $this->authorize('view', $laboratoryTest);
        return LaboratoryTestResource::make($laboratoryTest);
    }

    /**
     * Взятие в работу лабораторию
     * @param LaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setInWork(LaboratoryTest $laboratoryTest)
    {
        $this->authorize('set-in-work', $laboratoryTest);
        $uuid = $laboratoryTest->uuid;

        \DB::transaction(function () use ($uuid) {
            /**
             * @noinspection PhpUndefinedMethodInspection
             * @var LaboratoryTest $laboratoryTest
             */
            $laboratoryTest = LaboratoryTest::lockForUpdate()->where('uuid', $uuid)->firstOrFail();
            app(StatusTransitionManagerContract::class, compact('laboratoryTest'))->transition($this->user, LaboratoryTestStatus::ID_IN_WORK);
            $laboratoryTest->executorUser()->associate($this->user);
            $laboratoryTest->saveOrFail();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }
}
