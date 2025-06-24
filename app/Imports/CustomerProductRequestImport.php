<?php

namespace App\Imports;

use App\Exceptions\ClientException;
use App\Models\Assortment;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\User;
use App\Services\Management\ProductRequest\ProductRequestFactory;
use App\Services\Traits\CollectErrors;
use Carbon\Carbon;
use DB;
use Exception;
use Http\Client\Common\Exception\ClientErrorException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class CustomerProductRequestImport implements ToCollection
{
    use Importable, CollectErrors;

    /**
     * @var array
     */
    protected $importedProductRequestUuids;
    /**
     * @var User User
     */
    protected $supplierUser;
    /**
     * @var array
     */
    protected $products = [];
    /**
     * @var Carbon
     */
    protected $expectedDeliveryDate;

    /**
     * CustomerProductRequestImport constructor.
     * @param User $supplierUser
     * @param Collection $importedProductRequestUuids
     * @param bool $isSendErrorsArray
     */
    public function __construct(User $supplierUser, Collection $importedProductRequestUuids, $isSendErrorsArray = false)
    {
        $this->supplierUser = $supplierUser;
        $this->importedProductRequestUuids = $importedProductRequestUuids;
        $this->setIsSendErrorsArray($isSendErrorsArray);
    }

    /**
     * @param Collection|array $rows
     * @throws Exception
     * @throws Throwable
     */
    public function collection(Collection $rows)
    {
        // @todo Сделать так, чтобы ячейка с датой A1 не превращалась в какое-то число после парсинга

        $expectedDeliveryDate = (string) $rows->shift()[0];
        if (!preg_match('/^(\d{1,2}\.\d{1,2}\.\d{2,4})$/', $expectedDeliveryDate)) {
            throw new BadRequestHttpException('Неверный формат даты. Верный пример: \'01.01.2020\', поле с датой должно иметь Текстовый фортат данных');
        }
        $expectedDeliveryDate .= ' 23:59:59';
        $expectedDeliveryDate = Carbon::parse($expectedDeliveryDate);
        // Удаляем Итого
        $rows->pop();

        $emails = array_slice($rows->shift()->toArray(), 2);
        $emails = array_filter($emails);

        // Меняем местами элементы матрицы по диагонали (zip) (с лево-низ -> право верх)
        $rows = array_map(null, ...$rows->toArray());
        $barcodes = array_map(static function ($value) {
            return $value === null ? $value : (string) $value;
        }, array_shift($rows));

        $barcodes = array_filter($barcodes);

        // Названия продуктов
        array_shift($rows);

        DB::beginTransaction();
        foreach ($emails as $emailKey => $email) {
            $sendEmailOnException = ' Email: "' . $email . '"';
            /**
             * @var $customerUser User
             */
            $customerUser = User::where(['email' => $email])->first();
            if (!$customerUser) {
                $this->setOrThrowException(new BadRequestHttpException("Пользователь с email: '$email' не найден"));
                break;
            }

            $productsByBarcode = Product::query()
                ->select(['products.uuid', 'assortment_barcodes.barcode'])
                ->distinct()
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
                ->join('assortment_barcodes', static function (JoinClause $join) use ($barcodes) {
                    $join->on('assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
                        ->whereIn('assortment_barcodes.barcode', $barcodes);
                })
                ->join('price_list_product', 'price_list_product.product_uuid', '=', 'products.uuid')
                ->join('price_lists', function (JoinClause $join) {
                    $join->on('price_lists.uuid', '=', 'price_list_product.price_list_uuid')
                        ->where('price_lists.user_uuid', '=', $this->supplierUser->uuid)
                        ->where('price_lists.price_list_status_id', '=', PriceListStatus::CURRENT);
                })
                ->where(['products.user_uuid' => $this->supplierUser->uuid])
                ->get();

            if ($productsByBarcode->count() === 0) {
                $this->setOrThrowException(new BadRequestHttpException('Продукты не найдены' . $sendEmailOnException));
                break;
            }

            $productsByBarcode = $productsByBarcode->groupBy('barcode');

            $products = [];
            foreach ($barcodes as $barcodeKey => $barcode) {
                $quantity = (float)Arr::get($rows, $emailKey . '.' . $barcodeKey, 0);
                if ($quantity) {
                    /**
                     * @var $assortment Assortment
                     */
                    $product = $productsByBarcode->get($barcode);
                    if ($product && $product->count() !== 0) {
                        $product->first();
                        $products[] = [
                            'product_uuid' => $product->first()->uuid,
                            'quantity' => $quantity,
                            'expected_delivery_date' => $expectedDeliveryDate
                        ];
                    } else {
                        $this->setOrThrowException(new BadRequestHttpException("Продукт по штрихкоду '$barcode' не найден" . $sendEmailOnException));
                    }
                }
            }

            $productRequestWrappers = $this->asProductRequestFactory(collect($products), $customerUser)
                ->make($this->getIsSendErrorsArray());

            if ($productRequestWrappers->count() === 0) {
                $this->setOrThrowException(new BadRequestHttpException('Ошибка в логике поиске продуктов' . $sendEmailOnException));
                break;
            }


            foreach ($productRequestWrappers as $productRequestWrapper) {
                $productRequestWrapper->setCustomerUser($customerUser);
                $productRequest = $productRequestWrapper->saveOrFail();
                if ($productRequest) {
                    $this->importedProductRequestUuids[$productRequest->uuid] = $productRequest->products->pluck('uuid');
                } else {
                    $this->mergeErrors($productRequestWrapper->getErrors());
                }
            }
        }
        if ($this->getErrors()) {
            DB::rollBack();

            $this->throwExceptionWithErrors();
        }

        DB::commit();
    }

    /**
     * @param Collection $products
     * @param User $customerUser
     * @return ProductRequestFactory
     */
    public function asProductRequestFactory(Collection $products, User $customerUser)
    {
        return app(ProductRequestFactory::class)
            ->setCustomerUserUuid($customerUser->uuid)
            ->setSupplierProductsRequestUuids(collect([]))
            ->setProductQuantity(
                $products->pluck('quantity', 'product_uuid')
            )
            ->setExpectedDeliveryDate(
                $products->pluck('expected_delivery_date', 'product_uuid')
            )
            ->setDeliveryMethodId(ProductRequestDeliveryMethod::ID_DELIVERY);
    }
}
