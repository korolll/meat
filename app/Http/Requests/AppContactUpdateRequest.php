<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppContactUpdateRequest extends FormRequest
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
            'email' => 'nullable|string|between:2,200',
            'call_center_number' => 'nullable|string|between:2,200',
            'social_network_instagram' => 'nullable|string|between:2,200',
            'social_network_vk' => 'nullable|string|between:2,200',
            'social_network_facebook' => 'nullable|string|between:2,200',
            'social_messenger_telegram' => 'nullable|string|between:2,200',
            'delivey_information' => 'nullable|string|between:2,20000',
            'ios_version' => 'nullable|string|between:2,20000',
            'android_version' => 'nullable|string|between:2,20000',
        ];
    }
}
