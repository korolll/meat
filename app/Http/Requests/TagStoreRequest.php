<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagStoreRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        /** @var Tag $tag */
        $tag = $this->tag;

        return [
            'name' => [
                'required',
                'string',
                'between:3,255',
                Rule::unique('tags', 'name')->ignore($tag),
            ],
            'fixed_in_filters' => 'required|boolean'
        ];
    }
}
