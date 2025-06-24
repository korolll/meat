<?php

namespace App\Http\Requests;

use App\Rules\AlphaSpace;
use Illuminate\Foundation\Http\FormRequest;

class DriverUpdateRequest extends FormRequest
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
            'email' => "required|between:5,50|email|unique:drivers,email,{$this->driver->uuid},uuid",
            'password' => 'required|string|min:6',
            'hired_on' => 'required|string|date', // @todo iso rule
            'fired_on' => 'nullable|string|date', // @todo iso rule
            'comment' => 'nullable|string|between:0,300',
            'license_number' => 'required|string|between:5,20|alpha_num',
        ];
    }
}
