<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromocodeStoreRequest extends FormRequest
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
            'name' => 'required|string',
            'description' => 'nullable|string',
            'discount_percent' => 'required|int',
            'min_price' => 'required|int',
            'enabled' => 'required|boolean',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ];
    }
}
