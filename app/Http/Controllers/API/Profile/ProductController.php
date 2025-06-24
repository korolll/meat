<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\QueryFilters\AssortmentPropertyFilterTrait;
use App\Http\Requests\ProductSetDeliveryWeekdaysRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateIsActiveRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ProductCollectionResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductController extends Controller
{
    use AssortmentPropertyFilterTrait;

    /**
     * @param Request $request
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('index-owned', Product::class);

        $query = $this->user->products()
            ->with(['files']);

        $this->indexAssortmentPropertyFilter($request, $query->getQuery(), true);

        return ProductCollectionResponse::create($query);
    }

    /**
     * @param ProductStoreRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(ProductStoreRequest $request)
    {
        if ($request->exists('*RequestFilters')) {
            return $this->index($request);
        }
        $this->authorize('create', Product::class);

        if ($this->checkNotUnique($this->user->uuid, $request->assortment_uuid)) {
            throw new UnprocessableEntityHttpException("Product with assortment_uuid: {$request->assortment_uuid} already exists");
        }

        $product = new Product();
        $product->user()->associate($this->user);
        $this->saveProduct($product, $request->validated(), true);

        return ProductResource::make($product);
    }

    /**
     * @param Product $product
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        return ProductResource::make($product);
    }

    /**
     * @param ProductUpdateRequest $request
     * @param Product $product
     * @return mixed
     * @throws \Throwable
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $this->saveProduct($product, $request->validated());

        return ProductResource::make($product);
    }

    /**
     * @param ProductSetDeliveryWeekdaysRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function setDeliveryWeekdays(ProductSetDeliveryWeekdaysRequest $request)
    {
        /** @var \Illuminate\Support\Collection|Product[] $products */
        $products = Product::whereIn('uuid', $request->getProductUuids())->get();

        foreach ($products as $product) {
            $product->delivery_weekdays = $request->delivery_weekdays;
        }

        $products->saveOrFail();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ProductUpdateIsActiveRequest $request
     * @param Product $product
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setIsActive(ProductUpdateIsActiveRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->is_active = (bool) Arr::get($request->validated(), 'is_active');

        $product->saveOrFail();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Models\Product $product
     * @param array               $attributes
     * @param bool                $attachToMatrix
     *
     * @throws \Throwable
     */
    protected function saveProduct(Product $product, array $attributes, bool $attachToMatrix = false)
    {
        $product->fill($attributes);

        $files = collect(Arr::get($attributes, 'files', []))->mapWithKeys(function ($f) {
            return [$f['uuid'] => ['public_name' => Arr::get($f, 'public_name', null)]];
        })->all();

        DB::transaction(function () use ($product, $files, $attachToMatrix) {
            $product->saveOrFail();
            $product->files()->sync($files);

            if ($attachToMatrix) {
                $product->user->assortmentMatrix()->attach($product->assortment_uuid);
            }
        });
    }

    private function checkNotUnique(string $userUuid, $assortmentUuid)
    {
        return Product::where([
            'user_uuid' => $userUuid,
            'assortment_uuid' => $assortmentUuid,
        ])->exists();
    }
}
