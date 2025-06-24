<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class PromoDescriptionStoreRequest extends FormRequest
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
            'title' => 'required|string|between:2,1000',
            'description' => 'required|string|between:2,10000',
            'logo_file_uuid' => [
                'nullable',
                'uuid',
                new FileExists(FileCategory::ID_PROMO_LOGO),
            ],

            'color' => 'nullable|string|between:2,255',
            'is_hidden' => 'required|boolean',
            'subtitle' => 'nullable|string|between:2,1000'
        ];
    }
}
