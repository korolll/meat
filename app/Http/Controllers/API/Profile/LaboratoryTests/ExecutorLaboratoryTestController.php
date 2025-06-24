<?php

namespace App\Http\Controllers\API\Profile\LaboratoryTests;

use App\Contracts\Management\LaboratoryTest\StatusTransitionManagerContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExecutorLaboratoryTestSetStatusRequest;
use App\Http\Resources\LaboratoryTestResource;
use App\Http\Responses\LaboratoryTestCollectionResponse;
use App\Models\File;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTests\ExecutorLaboratoryTest;
use App\Models\LaboratoryTestStatus;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class ExecutorLaboratoryTestController extends Controller
{
    /**
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function index()
    {
        $this->authorize('index', ExecutorLaboratoryTest::class);

        return LaboratoryTestCollectionResponse::create(
            $this->user->executorLaboratoryTests()
        );
    }

    /**
     * @param ExecutorLaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ExecutorLaboratoryTest $laboratoryTest)
    {
        $this->authorize('view', $laboratoryTest);
        return LaboratoryTestResource::make($laboratoryTest);
    }

    /**
     * @param ExecutorLaboratoryTestSetStatusRequest $request
     * @param ExecutorLaboratoryTest $laboratoryTest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setStatus(ExecutorLaboratoryTestSetStatusRequest $request, ExecutorLaboratoryTest $laboratoryTest)
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

            // Файлы сейвим только для выполненных
            if ($request->laboratory_test_status_id === LaboratoryTestStatus::ID_DONE) {
                $files = collect($request->executor_files ?: [])->mapWithKeys(function($f) {
                    $file = File::findOrFail($f['uuid']);
                    return [$f['uuid'] => [
                        'file_category_id' => $file->file_category_id,
                        'public_name' => Arr::get($f, 'public_name', null)
                    ]];
                })->all();

                $laboratoryTest->executorFiles()->sync($files);
            }

            $laboratoryTest->saveOrFail();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }
}
