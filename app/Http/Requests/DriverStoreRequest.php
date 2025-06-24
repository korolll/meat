<?php

namespace App\Http\Requests;

use App\Rules\AlphaSpace;
use Illuminate\Foundation\Http\FormRequest;

class DriverStoreRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'between:5,60', new AlphaSpace()],
            'email' => 'required|email|between:5,50|unique:drivers',
            'password' => 'required|string|between:8,50',
            'hired_on' => 'required|string|date', // @todo iso rule
            'fired_on' => 'nullable|string|date', // @todo iso rule
            'comment' => 'nullable|string|between:0,300',
            'license_number' => 'required|string|between:5,20|alpha_num',
        ];
    }
}
