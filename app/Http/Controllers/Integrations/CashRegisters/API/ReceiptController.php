<?php

namespace App\Http\Controllers\Integrations\CashRegisters\API;

use App\Events\ReceiptReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\CashRegisters\API\CalculateReceiptRequest;
use App\Http\Requests\Integrations\CashRegisters\API\StoreReceiptRequest;
use App\Models\LoyaltyCard;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface;
use App\Services\Management\Client\Product\PriceDataInterface;
use App\Services\Management\Client\Product\ProductItem;
use App\Services\Management\Client\Product\TargetEnum;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReceiptController extends Controller
{
    /**
     * @param \App\Http\Requests\Integrations\CashRegisters\API\CalculateReceiptRequest $request
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Throwable
     */
    public function calculateDiscount(CalculateReceiptRequest $request)
    {
        $validated = $request->validated();
        $store = User::findOrFail(Arr::get($validated, 'store_uuid'));
        $loyaltyCard = LoyaltyCard::findOrFail(Arr::get($validated, 'loyalty_card_uuid'));
        $client = $loyaltyCard->client;
        if (! $client) {
            throw new BadRequestHttpException('Client not found');
        }

        $items = Arr::get($validated, 'items');
        $articles = Arr::pluck($items, 'number');
        $products = $this->findProducts($store, $articles);
        $uuid = Arr::get($validated, 'uuid');

        $resultItems = [];
        $productItems = [];
        $conn = DB::connection();
        $conn->beginTransaction();
        $calcData = [];

        try {
            foreach ($items as $itemData) {
                /** @var Product|null $product */
                $product = $products->get($itemData['number']);
                $resultItems[$itemData['number']] = $itemData;
                if (! $product) {
                    continue;
                }

                // Hack with price
                if ($product->price != $itemData['price']) {
                    $product->price = $itemData['price'];
                    $product->save();
                }

                $productItems[] = ProductItem::create($product, (float)$itemData['count']);
            }

            $receiptLineFake = new ReceiptLine();
            $closure = function ($key, ProductItem $productItem, PriceDataInterface $data) use (&$resultItems, &$calcData, $receiptLineFake) {
                $product = $productItem->getProduct();
                $article = $product->assortment->article;
                $resultItems[$article]['price'] = $data->getPriceWithDiscount();
                $resultItems[$article]['sum'] = $data->getTotalAmountWithDiscount();

                $model = $data->getDiscountModel();
                if ($model) {
                    $receiptLineFake->discountable()->associate($model);
                    $calcDataRow = $receiptLineFake->only([
                        'discountable_type',
                        'discountable_uuid'
                    ]);
                    $calcDataRow['price_with_discount'] = $data->getPriceWithDiscount();
                    $calcDataRow['discount'] = $data->getDiscount();
                } else {
                    $calcDataRow = [];
                }

                if ($totalBonus = $data->getTotalBonus()) {
                    $calcDataRow['total_bonus'] = $totalBonus;
                }

                if ($calcDataRow) {
                    $calcData[$product->assortment_uuid] = $calcDataRow;
                }
            };

            /** @var ClientProductCollectionPriceCalculatorInterface $calculator */
            $calculator = app(ClientProductCollectionPriceCalculatorInterface::class);
            $ctx = new CalculateContext(
                $client,
                TargetEnum::RECEIPT
            );
            $priceData = $calculator->calculate($ctx, $productItems, $closure);
        } finally {
            $conn->rollBack();
        }

        if ($uuid) {
            $ttl = config('app.receipt.discount.cash_reg_cache_ttl');
            Cache::set($this->makeCacheKey($uuid), $calcData, $ttl);
        }

        return [
            'loyalty_card_uuid' => $loyaltyCard->uuid,
            'store_uuid' => $store->uuid,
            'items' => array_values($resultItems),
            'total' => Arr::get($validated, 'total'),
            'total_with_discount' => $priceData->getTotalPriceWithDiscount(),
            'discount' => $priceData->getTotalDiscount(),
        ];
    }

    /**
     * @param \App\Http\Requests\Integrations\CashRegisters\API\StoreReceiptRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StoreReceiptRequest $request)
    {
        $validated = $request->validated();
        $store = User::findOrFail(Arr::get($validated, 'store_uuid'));
        $loyaltyCardUuid = Arr::get($validated, 'loyalty_card_uuid');
        $loyaltyCard = null;
        if ($loyaltyCardUuid) {
            $loyaltyCard = LoyaltyCard::find($loyaltyCardUuid);
        }

        $uuid = Arr::get($validated, 'uuid');
        $receipt = new Receipt();
        $receipt->forceFill([
            'uuid' => $uuid,
            'user_uuid' => $store->uuid,
            'id' => Arr::get($validated, 'receipt_id', 0),
            'receipt_package_id' => Arr::get($validated, 'receipt_package_id', 0),
            'loyalty_card_number' => $loyaltyCard ? $loyaltyCard->number : null,
            'loyalty_card_uuid' => $loyaltyCard ? $loyaltyCard->uuid : null,
            'loyalty_card_type_uuid' => $loyaltyCard ? $loyaltyCard->loyalty_card_type_uuid : null,
            'refund_by_receipt_uuid' => Arr::get($validated, 'refund_by_receipt_uuid'),
            'total' => Arr::get($validated, 'total'),
            'created_at' => now(),
        ]);

        $cacheKey = null;
        $calcData = [];
        if ($uuid) {
            $cacheKey = $this->makeCacheKey($uuid);
            $calcData = Cache::get($cacheKey, []);
        }

        $items = Arr::get($validated, 'items');
        $articles = Arr::pluck($items, 'number');
        $products = $this->findProducts($store, $articles);
        DB::transaction(function () use ($items, $receipt, $products, $calcData) {
            /**
             * @var \Illuminate\Database\Eloquent\Collection $collection
             */
            list($collection, $bonus) = $this->createReceiptLines($items, $receipt, $products, $calcData);
            if ($bonus) {
                $receipt->forceFill([
                    'total_bonus' => $bonus,
                    'bonus_to_charge' => $bonus
                ]);
            }

            $receipt->save();
            $collection->saveOrFail();
            ReceiptReceived::dispatch($receipt);
        });

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        return response('', Response::HTTP_CREATED);
    }

    /**
     * @param \App\Models\User $store
     * @param array            $articles
     *
     * @return \Illuminate\Support\Collection
     */
    protected function findProducts(User $store, array $articles): Collection
    {
        return Product::whereUserUuid($store->uuid)
            ->join('assortments', 'assortments.uuid', 'assortment_uuid')
            ->with('assortment')
            ->whereIn('assortments.article', $articles)
            ->get('products.*')
            ->keyBy(function (Product $product) {
                return $product->assortment->article;
            });
    }

    /**
     * @param array                          $items
     * @param \App\Models\Receipt            $receipt
     * @param \Illuminate\Support\Collection $products
     * @param array                          $calcData
     *
     * @return array
     */
    protected function createReceiptLines(array $items, Receipt $receipt, Collection $products, array $calcData): array
    {
        $totalBonus = 0;
        $collection = new \Illuminate\Database\Eloquent\Collection();
        foreach ($items as $itemData) {
            $receiptLine = new ReceiptLine();
            $receiptLine->forceFill([
                'barcode' => '',

                'total' => $itemData['sum'],
                'quantity' => $itemData['count'],

                'price_with_discount' => $itemData['price'],
                'discount' => 0,
            ]);

            /** @var Product $product */
            $product = $products->get($itemData['number']);
            if ($product) {
                $receiptLine->assortment_uuid = $product->assortment_uuid;
                $receiptLine->product_uuid = $product->uuid;

                $calculateData = Arr::get($calcData, $receiptLine->assortment_uuid);
                if ($calculateData) {
                    $receiptLine->forceFill($calculateData);
                    $totalBonus += $calculateData['total_bonus'] ?? 0;
                }
            }

            $receiptLine->receipt()->associate($receipt);
            $collection->add($receiptLine);
        }

        return [$collection, $totalBonus];
    }

    /**
     * @param string $uuid
     *
     * @return string
     */
    protected function makeCacheKey(string $uuid): string
    {
        return 'cash_reg_receipt_discount:' . $uuid;
    }
}
