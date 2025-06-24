<?php

namespace App\Http\Requests\Clients\API;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginViaPhoneRequest extends FormRequest
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
            'phone' => ['required', new PhoneNumber()],
            'code' => 'digits:4',
        ];
    }
}
