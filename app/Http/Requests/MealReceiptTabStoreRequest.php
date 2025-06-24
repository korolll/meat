<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;

class MealReceiptTabStoreRequest extends FormRequest
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
            'meal_receipt_uuid' => 'required|uuid|exists:meal_receipts,uuid',
            'title' => 'nullable|string',
            'text' => 'nullable|string',
            'url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'sequence' => 'required|integer|min:0',
            'text_color' => 'nullable|string|min:1',
            'button_title' => 'nullable|string|min:1',
            'file_uuid' => [
                'required',
                'uuid',
                new FileExists(FileCategory::ID_MEAL_RECEIPT_FILE),
            ],
        ];
    }
}
