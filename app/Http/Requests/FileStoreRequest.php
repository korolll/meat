<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileStoreRequest extends FormRequest
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
            'file_category_id' => 'required|string|exists:file_categories,id',
            'file' => $this->getFileRules(),
        ];
    }

    /**
     * @return string
     */
    protected function getFileRules()
    {
        return implode('|', array_filter([
            'required|file',
            config("app.file.upload-rules.{$this->file_category_id}", ''),
        ]));
    }
}
