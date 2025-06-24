<?php

namespace App\Http\Requests\Clients\API\Profile;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteStoreStoreRequest extends FormRequest
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
            'store_uuid' => [
                'required',
                'uuid',
                Rule::exists('users', 'uuid')
                    ->where('user_type_id', UserType::ID_STORE)
            ],
        ];
    }
}
