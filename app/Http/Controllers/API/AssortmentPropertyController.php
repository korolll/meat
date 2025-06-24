<?php

namespace App\Http\Controllers\API;

use App\Contracts\Models\Assortment\Property\CastDataTypeFactoryContract;
use App\Exceptions\ClientExceptions\AssortmentPropertyDataTypeNotEnumException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssortmentPropertyAddAvailableValueRequest;
use App\Http\Requests\AssortmentPropertyChangeDataTypeRequest;
use App\Http\Requests\AssortmentPropertyRemoveAvailableValueRequest;
use App\Http\Requests\AssortmentPropertyStoreRequest;
use App\Http\Resources\AssortmentPropertyResource;
use App\Http\Responses\AssortmentPropertyCollectionResponse;
use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class AssortmentPropertyController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('index', AssortmentProperty::class);

        $query = AssortmentProperty::query();

        $this->indexDefaultFilterByIsSearchable($request, $query);

        return AssortmentPropertyCollectionResponse::create($query);
    }

    private function indexDefaultFilterByIsSearchable(Request $request, $query)
    {
        if ($request->exists('where') && is_array($request->get('where'))){
            /**
             * @var $query Builder
             */
            $isDefaultSearchableFilter = true;
            foreach ($request->get('where') as $whereParam) {
                if (in_array('is_searchable', $whereParam, true)){
                    $isDefaultSearchableFilter = false;
                    break;
                }
            }

            if ($isDefaultSearchableFilter){
                $query->where('is_searchable', '=', true);
            }
        }
    }

    /**
     * @param AssortmentPropertyStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(AssortmentPropertyStoreRequest $request)
    {
        $this->authorize('create', AssortmentProperty::class);

        $assortmentProperty = new AssortmentProperty($request->validated());
        $assortmentProperty->saveOrFail();

        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AssortmentProperty $assortmentProperty)
    {
        $this->authorize('view', $assortmentProperty);

        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentPropertyStoreRequest $request
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(AssortmentPropertyStoreRequest $request, AssortmentProperty $assortmentProperty)
    {
        $this->authorize('update', $assortmentProperty);

        $assortmentProperty->fill($request->validated());
        $assortmentProperty->saveOrFail();

        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(AssortmentProperty $assortmentProperty)
    {
        $this->authorize('delete', $assortmentProperty);

        $assortmentProperty->delete();

        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentPropertyChangeDataTypeRequest $request
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function changeDataType(AssortmentPropertyChangeDataTypeRequest $request, AssortmentProperty $assortmentProperty)
    {
        $this->authorize('change-data-type', $assortmentProperty);
        $caster = app(CastDataTypeFactoryContract::class)->make(
            $assortmentProperty->assortment_property_data_type_id,
            $request->assortment_property_data_type_id
        );
        $assortmentProperty = $caster->cast($assortmentProperty);
        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentPropertyAddAvailableValueRequest $request
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws AssortmentPropertyDataTypeNotEnumException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addAvailableValue(AssortmentPropertyAddAvailableValueRequest $request, AssortmentProperty $assortmentProperty)
    {
        $this->authorize('add-available-value', $assortmentProperty);
        if ($assortmentProperty->assortment_property_data_type_id !== AssortmentPropertyDataType::ID_ENUM) {
            throw new AssortmentPropertyDataTypeNotEnumException();
        }
        $availableValues = $assortmentProperty->available_values ?: [];
        $availableValues[] = $request->value;
        $assortmentProperty->available_values = $availableValues;
        $assortmentProperty->save();
        return AssortmentPropertyResource::make($assortmentProperty);
    }

    /**
     * @param AssortmentPropertyRemoveAvailableValueRequest $request
     * @param AssortmentProperty $assortmentProperty
     * @return mixed
     * @throws AssortmentPropertyDataTypeNotEnumException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeAvailableValue(AssortmentPropertyRemoveAvailableValueRequest $request, AssortmentProperty $assortmentProperty)
    {
        $this->authorize('remove-available-value', $assortmentProperty);
        if ($assortmentProperty->assortment_property_data_type_id !== AssortmentPropertyDataType::ID_ENUM) {
            throw new AssortmentPropertyDataTypeNotEnumException();
        }

        $key = array_search($request->value, $assortmentProperty->available_values);
        $availableValues = $assortmentProperty->available_values;
        unset($availableValues[$key]);
        $assortmentProperty->available_values = $availableValues;
        $assortmentProperty->save();

        return AssortmentPropertyResource::make($assortmentProperty);
    }
}
