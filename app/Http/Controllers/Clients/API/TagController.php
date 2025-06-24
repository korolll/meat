<?php

namespace App\Http\Controllers\Clients\API;

use App\Contracts\Database\ToQueryTransformerContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\TagSearchRequest;
use App\Http\Resources\TagNameResource;
use App\Http\Resources\TagResource;
use App\Http\Responses\TagCollectionResponse;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
//        $this->authorize('index', Tag::class);

        return TagCollectionResponse::create(
            Tag::query()
        );
    }

    /**
     * @param Tag $tag
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Tag $tag)
    {
//        $this->authorize('view', $tag);
        return TagResource::make($tag);
    }

    /**
     * @param TagSearchRequest $request
     * @param ToQueryTransformerContract $toQueryTransformer
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(TagSearchRequest $request, ToQueryTransformerContract $toQueryTransformer)
    {
//        $this->authorize('search', Tag::class);

        $query = Tag::search(
            $toQueryTransformer->transform($request->phrase)
        );

        // Пагинация
        $page = (int)$request->page ?: 1;
        $size = (int)$request->per_page ?: 10;

        // todo fix it after EloquentCollectionResponse refactoring
        return TagNameResource::collection(
            $query->paginate($size, 'page', $page)
        );
    }
}
