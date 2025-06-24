<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class LoyaltyCardTypeStoreRequest extends FormRequest
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
            'name' => 'required|string|between:2,60',
            'logo_file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_LOYALTY_CARD_TYPE_LOGO),
            ],
        ];
    }
}
