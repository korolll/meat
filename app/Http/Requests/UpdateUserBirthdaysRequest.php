<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserBirthdaysRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'birthdays' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $key => $date) {
                        if (!is_numeric($key) || $key <= 0 || floor($key) != $key) {
                            $fail("Некорректный ID");
                            return;
                        }
                    }
                }
            ],
            'birthdays.*' => [
                'required',
                'date_format:Y-m-d',
                'before:today',
            ]
        ];
    }

    public function messages()
    {
        return [
            'birthdays.required' => 'Массив обязателен',
            'birthdays.array' => 'Массив обязателен',
            'birthdays.*.required' => 'Дата обязательна',
            'birthdays.*.date_format' => 'Некорректная дата',
            'birthdays.*.before' => 'Некорректная дата'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'status' => 'error',
            'message' => 'Ошибка валидации',
            'errors' => $validator->errors()
        ], 422));
    }
} 