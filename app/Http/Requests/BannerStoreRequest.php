<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class BannerStoreRequest extends FormRequest
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
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'number' => 'required|int',
            'enabled' => 'required|boolean',
            'reference_uuid' => 'nullable|uuid',
            'reference_type' => 'nullable|string',
            'logo_file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_BANNER_IMAGE),
            ]
        ];
    }
}
