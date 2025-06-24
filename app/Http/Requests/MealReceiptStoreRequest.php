<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class MealReceiptStoreRequest extends FormRequest
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
            'name' => 'required|string|min:1',
            'section' => 'required|string|min:1',
            'title' => 'required|string|min:1',
            'description' => 'required|string|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|min:1',
            'ingredients.*.quantity' => 'required|string|min:1',
            'duration' => 'nullable|integer|between:1,99999999',
            'file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_MEAL_RECEIPT_FILE),
            ],
            'assortment_uuids' => 'array',
            'assortment_uuids.*' => 'required|uuid|exists:assortments,uuid',
        ];
    }
}
