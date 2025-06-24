<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNotificationRequest extends FormRequest
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
            'title' => 'required|string|between:3,255',
            'body' => 'required|string|between:3,1024',
            'meta' => 'array|min:0',
            'client_uuids' => 'required|array|min:1',
            'client_uuids.*' => 'required|string|distinct|uuid|exists:clients,uuid',
        ];
    }
}
