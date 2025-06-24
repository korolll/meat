<?php

namespace App\Http\Requests\API\Orders;

use App\Models\Order;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class StoreOrderProductRequest extends FormRequest
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
        $orderUuid = $this->get('order_uuid');
        $assortmentUuidRules = [
            'required',
            'uuid'
        ];
        if (is_scalar($orderUuid) && Uuid::isValid((string)$orderUuid)) {
            /** @var \StdClass $orderStore */
            $orderStore = Order::whereUuid($orderUuid)
                ->toBase()
                ->select('store_user_uuid')
                ->first();
            if ($orderStore) {
                $assortmentUuidRules[] = Rule::exists('products', 'assortment_uuid')
                    ->where(function (Builder $query) use ($orderStore) {
                        return $query
                            ->where('user_uuid', $orderStore->store_user_uuid)
                            ->where('price', '>', 0);
                    });
            }
        }

        return [
            'quantity' => 'required|numeric|between:0.05,100',
            'assortment_uuid' => $assortmentUuidRules,
            'order_uuid' => 'required|uuid|exists:orders,uuid',
        ];
    }
}
