<?php

namespace App\Http\Controllers\API\Profile\LaboratoryTests;

use App\Contracts\Management\LaboratoryTest\StatusTransitionManagerContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLaboratoryTestSetStatusRequest;
use App\Http\Requests\CustomerLaboratoryTestStoreRequest;
use App\Http\Resources\LaboratoryTestResource;
use App\Http\Responses\LaboratoryTestCollectionResponse;
use App\Models\File;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTests\CustomerLaboratoryTest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CustomerLaboratoryTestController extends Controller
{
    /**
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function index()
    {
        $this->authorize('index', CustomerLaboratoryTest::class);

        return LaboratoryTestCollectionResponse::create(
            $this->user->customerLaboratoryTests()
        );
    }

    /**
     * @param CustomerLaboratoryTestStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CustomerLaboratoryTestStoreRequest $request)
    {
        $this->authorize('create', CustomerLaboratoryTest::class);

        $attributes = $request->validated();
        $laboratoryTest = new CustomerLaboratoryTest($attributes);
        $laboratoryTest->customerUser()->associate($this->user);

        $laboratoryTest = $this->saveWithFiles($laboratoryTest, $attributes);
        return LaboratoryTestResource::make($laboratoryTest);
    }

    /**
     * @param CustomerLaboratoryTestStoreRequest $request
     * @param CustomerLaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(CustomerLaboratoryTestStoreRequest $request, CustomerLaboratoryTest $laboratoryTest)
    {
        $this->authorize('update', $laboratoryTest);

        $attributes = $request->validated();
        $laboratoryTest->fill($attributes);
        $laboratoryTest = $this->saveWithFiles($laboratoryTest, $attributes);

        return LaboratoryTestResource::make($laboratoryTest);
    }

    /**
     * @param CustomerLaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CustomerLaboratoryTest $laboratoryTest)
    {
        $this->authorize('view', $laboratoryTest);
        return LaboratoryTestResource::make($laboratoryTest);
    }

    /**
     * @param CustomerLaboratoryTestSetStatusRequest $request
     * @param CustomerLaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setStatus(CustomerLaboratoryTestSetStatusRequest $request, CustomerLaboratoryTest $laboratoryTest)
    {
        $this->authorize('set-status', $laboratoryTest);
        $uuid = $laboratoryTest->uuid;

        \DB::transaction(function () use ($uuid, $request) {
            /**
             * @noinspection PhpUndefinedMethodInspection
             * @var LaboratoryTest $laboratoryTest
             */
            $laboratoryTest = LaboratoryTest::lockForUpdate()->where('uuid', $uuid)->firstOrFail();
            app(StatusTransitionManagerContract::class, compact('laboratoryTest'))->transition($this->user, $request->laboratory_test_status_id);
            $laboratoryTest->saveOrFail();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param LaboratoryTest $laboratoryTest
     * @param array $attributes
     * @return LaboratoryTest
     * @throws \Throwable
     */
    protected function saveWithFiles(LaboratoryTest $laboratoryTest, array $attributes): LaboratoryTest
    {
        $files = collect(Arr::get($attributes, 'customer_files', []))->mapWithKeys(function($f) {
            $file = File::findOrFail($f['uuid']);
            return [$f['uuid'] => [
                'file_category_id' => $file->file_category_id,
                'public_name' => Arr::get($f, 'public_name', null)
            ]];
        })->all();

        \DB::transaction(function () use ($laboratoryTest, $files) {
            $laboratoryTest->saveOrFail();
            $laboratoryTest->customerFiles()->sync($files);
        });

        return $laboratoryTest;
    }
}
