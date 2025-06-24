<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UserPasswordResetSetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required|string',
            'password' => 'required|string|between:8,50',
        ];
    }

    /**
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $payload = decrypt($this->token);

        return [
            'email' => Arr::get($payload, 'email', ''),
            'token' => Arr::get($payload, 'token', ''),
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ];
    }
}
