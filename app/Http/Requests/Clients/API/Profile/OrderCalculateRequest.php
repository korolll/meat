<?php

namespace App\Http\Requests\Clients\API\Profile;

use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class OrderCalculateRequest extends FormRequest
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
//        $sentOrder = $this->get('order');
//        $storeUuid = Arr::get($sentOrder, 'store_user_uuid');

        $assortmentUuidsRules = [
            'required',
            'uuid',
            'distinct',
        ];

        /**
         * To do only one request we need to validate it custom-way.
         * @see \App\Http\Controllers\Clients\API\Profile\OrderController::validateProductUuids
         */
//        if (is_scalar($storeUuid) && Uuid::isValid((string)$storeUuid)) {
//            $assortmentUuidsRules[] = Rule::exists('products', 'assortment_uuid')
//                ->where(function (Builder $query) use ($storeUuid) {
//                    return $query
//                        ->where('user_uuid', $storeUuid)
//                        ->where('price', '>', 0);
//                });
//        }

        return [
            'order.promocode' => 'nullable|string|between:2,255',
            'order.store_user_uuid' => 'required|uuid|exists:users,uuid',

            'order.order_delivery_type_id' => 'nullable|string|exists:order_delivery_types,id',
            'order.order_payment_type_id' => 'nullable|string|exists:order_payment_types,id',

            'order.paid_bonus' => 'nullable|integer|min:1',

            'products' => 'required|array|min:1',
            'products.*.quantity' => 'required|numeric|between:0.001,100',
            'products.*.assortment_uuid' => $assortmentUuidsRules,
        ];
    }

    /**
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();
        $order = &$validated['order'];

        if (! Arr::get($order, 'order_delivery_type_id')) {
            $order['order_delivery_type_id'] = OrderDeliveryType::ID_PICKUP;
        }
        if (! Arr::get($order, 'order_payment_type_id')) {
            $order['order_payment_type_id'] = OrderPaymentType::ID_CASH;
        }

        return $validated;
    }
}
