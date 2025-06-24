<?php

namespace App\Http\Controllers\API\Profile;

use App\Contracts\Models\Draft\CreateDraftContract;
use App\Contracts\Models\Draft\DeleteDraftByNameContract;
use App\Contracts\Models\Draft\FindDraftByNameContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\DraftStoreRequest;
use Illuminate\Http\Response;

class DraftController extends Controller
{
    /**
     * @param DraftStoreRequest $request
     * @param CreateDraftContract $creator
     * @return mixed
     */
    public function store(DraftStoreRequest $request, CreateDraftContract $creator)
    {
        $creator->create($this->user, $request->name, $request->get('attributes'));

        return response('', Response::HTTP_CREATED);
    }

    /**
     * @param string $name
     * @param FindDraftByNameContract $finder
     * @return mixed
     */
    public function show(string $name, FindDraftByNameContract $finder)
    {
        $draft = $finder->find($this->user, $name);

        return ['data' => $draft->only('name', 'attributes')];
    }

    /**
     * @param string $name
     * @param DeleteDraftByNameContract $remover
     * @return mixed
     */
    public function destroy(string $name, DeleteDraftByNameContract $remover)
    {
        $remover->delete($this->user, $name);

        return response('', Response::HTTP_OK);
    }
}
