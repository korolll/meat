<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class StoryStoreRequest extends FormRequest
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
            'story_name' => 'nullable|string',
            'show_from' => 'required|string|date_format:Y-m-d H:i:sO|before:show_to',
            'show_to' => 'required|string|date_format:Y-m-d H:i:sO',
            'logo_file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_STORY_IMAGE),
            ]
        ];
    }
}
