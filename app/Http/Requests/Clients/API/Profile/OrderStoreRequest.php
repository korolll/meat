<?php

namespace App\Http\Requests\Clients\API\Profile;

use App\Models\OrderDeliveryType;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;
use Throwable;

class OrderStoreRequest extends OrderCalculateRequest
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
            'required',
            'date_format:Y-m-d H:i:sO',
            'after:planned_delivery_datetime_from',
        ];

        $order = $this->get('order', []);
        $dateFrom = (string)Arr::get($order, 'planned_delivery_datetime_from');
        $dateFrom = $this->tryParseDate($dateFrom);
        if ($dateFrom) {
            $validationDatetimeTo[] = function ($attribute, $value, $fail) use ($dateFrom) {
                $dateTo = $this->tryParseDate((string)$value);
                if ($dateTo && $dateTo->format('Y-m-d') !== $dateFrom->format('Y-m-d')) {
                    $fail($attribute . ' should be in the same day as order.planned_delivery_datetime_from');
                }
            };
        }

        /** @var \App\Models\Client $client */
        $client = auth()->user();
        return array_merge(parent::rules(), [
            'order.promocode' => 'nullable|string|between:2,255',
            'order.client_comment' => 'nullable|between:2,255',
            'order.client_email' => 'required|email',

            'order.client_address_data.address' => 'required_if:order.order_delivery_type_id,' . OrderDeliveryType::ID_DELIVERY .'|string|between:5,512',
            'order.client_address_data.floor' => 'integer',
            'order.client_address_data.entrance' => 'integer',
            'order.client_address_data.apartment_number' => 'integer',
            'order.client_address_data.intercom_code' => 'string|between:1,16',

            'order.planned_delivery_datetime_from' => 'required|date_format:Y-m-d H:i:sO|after:now',
            'order.planned_delivery_datetime_to' => $validationDatetimeTo,

            'order.client_credit_card_uuid' => [
                'nullable',
                'uuid',
                Rule::exists('client_credit_cards', 'uuid')
                    ->where('client_uuid', $client->uuid)
                    ->whereNotNull('binding_id')
            ]
        ]);
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
