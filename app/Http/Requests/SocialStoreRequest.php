<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class SocialStoreRequest extends FormRequest
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
            'title' => 'required|string|between:1,60',
            'sort_number' => 'nullable|integer|between:1,9999',
            'url' => 'required|string|between:2,200',
            'logo_file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_SOCIAL_LOGO),
            ],
        ];
    }
}
