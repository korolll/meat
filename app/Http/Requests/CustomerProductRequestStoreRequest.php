<?php

namespace App\Http\Requests;

use App\Rules\AvailableForRequestProductExists;
use App\Rules\SupplierProductRequestSuitableForNewOrderExists;
use App\Services\Management\ProductRequest\ProductRequestFactoryContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;

/**
 * Class CustomerProductRequestStoreRequest
 *
 * @property array $supplier_product_requests
 * @property array $products
 * @property string $product_request_delivery_method_id
 *
 * @package App\Http\Requests
 */
class CustomerProductRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Заявки на отгрузку из которых создается новый заказ (могут отсутствовать)
            'supplier_product_requests' => 'array',
            'supplier_product_requests.*.uuid' => [
                'required',
                'uuid',
                'distinct',
                new SupplierProductRequestSuitableForNewOrderExists(),
            ],

            // Список продуктов для заказа
            'products' => [
                'required',
                'array',
                new AvailableForRequestProductExists(user())
            ],
            'products.*.product_uuid' => [
                'required',
                'uuid',
                'distinct',
            ],
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.expected_delivery_date' => 'required|string|date|after_or_equal:now', // @todo iso rule

            // Требуемый метод доставки, возможно клиент хочет самовывоз
            'product_request_delivery_method_id' => 'required|exists:product_request_delivery_methods,id',
        ];
    }

    /**
     * @return ProductRequestFactoryContract
     */
    public function asProductRequestFactory()
    {
        $supplierProductRequests = collect($this->get('supplier_product_requests', []));
        $products = collect($this->products);

        return app(ProductRequestFactoryContract::class)->setSupplierProductsRequestUuids(
            $supplierProductRequests->pluck('uuid')
        )->setProductQuantity(
            $products->pluck('quantity', 'product_uuid')
        )->setExpectedDeliveryDate(
            $products->pluck('expected_delivery_date', 'product_uuid')->map(function (string $date) {
                return Date::parse($date);
            })
        )->setDeliveryMethodId($this->product_request_delivery_method_id);
    }
}
