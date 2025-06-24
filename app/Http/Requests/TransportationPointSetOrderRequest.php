<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class TransportationPointSetOrderRequest extends FormRequest
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
            'transportation_points.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                'exists:transportation_points,uuid',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getOrderedTransportationPointUuids()
    {
        return Arr::pluck($this->transportation_points, 'uuid');
    }
}
