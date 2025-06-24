<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;
class StoryTabStoreRequest extends FormRequest
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
            'story_id' => 'required|integer|exists:stories,id',
            'title' => 'nullable|string',
            'text' => 'nullable|string',
            'url' => 'nullable|string',
            'duration' => 'required|integer',
            'text_color' => 'nullable|string',
            'button_title' => 'nullable|string',
            'logo_file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_STORY_IMAGE),
            ],
        ];
    }
}
