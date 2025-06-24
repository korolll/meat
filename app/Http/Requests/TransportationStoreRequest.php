<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class TransportationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => 'required|string|date|after_or_equal:now', // @todo iso rule
            'car_uuid' => [
                'required',
                'uuid',
                Rule::exists('cars', 'uuid')->where('user_uuid', user()->uuid),
            ],
            'driver_uuid' => [
                'required',
                'uuid',
                Rule::exists('drivers', 'uuid')->where('user_uuid', user()->uuid),
            ],
            'product_requests.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                Rule::exists('product_requests')->where(function (Builder $query) {
                    $query->where('delivery_user_uuid', user()->uuid);
                    $query->whereNull('transportation_uuid');

                    return $query;
                }),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getProductRequestUuids()
    {
        return Arr::pluck($this->product_requests, 'uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductRequests()
    {
        return user()->deliveryProductRequests()->whereIn('uuid', $this->getProductRequestUuids())->get();
    }
}
