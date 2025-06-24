<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationTaskStoreRequest extends FormRequest
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
            'title_template' => 'required|string|between:3,255',
            'body_template' => 'required|string|min:3',
            'options' => 'nullable',
            'execute_at' => 'required|date_format:"Y-m-d H:i:sO"|after:now',

            'client_uuids' => 'nullable|array',
            'client_uuids.*' => 'required|uuid|distinct|exists:clients,uuid'
        ];
    }
}
