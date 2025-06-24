<?php

namespace App\Http\Requests;

use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Throwable;

class OrderUpdateRequest extends FormRequest
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
        $validationDatetimeTo = [
            'required_with:planned_delivery_datetime_to',
            'date_format:Y-m-d H:i:sO',
            'after:planned_delivery_datetime_from',
        ];

        $dateFrom = (string)$this->get( 'planned_delivery_datetime_from', '');
        $dateFrom = $this->tryParseDate($dateFrom);
        if ($dateFrom) {
            $validationDatetimeTo[] = function ($attribute, $value, $fail) use ($dateFrom) {
                $dateTo = $this->tryParseDate((string)$value);
                if ($dateTo && $dateTo->format('Y-m-d') !== $dateFrom->format('Y-m-d')) {
                    $fail($attribute . ' should be in the same day as planned_delivery_datetime_from');
                }
            };
        }

        return [
            'order_delivery_type_id' => 'string|exists:order_delivery_types,id',
            'courier_phone' => 'nullable|string|between:2,255',
            'order_payment_type_id' => 'string|exists:order_payment_types,id',

            'client_comment' => 'nullable|between:2,255',
            'client_email' => 'email',

            'client_address_data.address' => 'string|between:10,512',
            'client_address_data.floor' => 'integer',
            'client_address_data.entrance' => 'integer',
            'client_address_data.apartment_number' => 'integer',
            'client_address_data.intercom_code' => 'string|between:4,16',

            'planned_delivery_datetime_from' => 'required_with:planned_delivery_datetime_from|date_format:Y-m-d H:i:sO|after:today',
            'planned_delivery_datetime_to' => $validationDatetimeTo,
        ];
    }

    /**
     * @param string $date
     *
     * @return \Carbon\CarbonInterface|null
     */
    protected function tryParseDate(string $date): ?CarbonInterface
    {
        if (! $date) {
            return null;
        }
        try {
            return Date::parse($date);
        } catch (Throwable $e) {
            return null;
        }
    }
}
