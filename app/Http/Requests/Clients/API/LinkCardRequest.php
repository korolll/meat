<?php

namespace App\Http\Requests\Clients\API;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'store_uuid' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'uuid')
                    ->where('user_type_id', UserType::ID_STORE)
            ],
        ];
    }
}
