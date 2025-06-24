<?php

namespace App\Services\Integrations\Frontol;

use App\Events\ReceiptReceived;
use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\ClientPromotion;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCode;
use App\Models\Product;
use App\Models\PromoDescription;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoYellowPrice;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use App\Notifications\Clients\API\AuthenticationCode;
use App\Notifications\Clients\API\FrontolReceiptReceived;
use App\Rules\PhoneNumber;
use App\Services\Authentication\ClientAuthenticationManagerContract;
use App\Services\Management\Client\Bonus\MaxBonusesCalculatorInterface;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface;
use App\Services\Management\Client\Product\CollectionPriceData;
use App\Services\Management\Client\Product\CollectionPriceDataInterface;
use App\Services\Management\Client\Product\Discount\Concrete\FrontolInMemoryDiscount;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use App\Services\Management\Client\Product\PriceDataInterface;
use App\Services\Management\Client\Product\ProductItem;
use App\Services\Management\Client\Product\TargetEnum;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Ramsey\Uuid\Uuid;

class LoyaltySystem implements LoyaltySystemInterface
{
    const SEARCH_BY_PHONE = 'by_phone';
    const SEARCH_BY_CARD = 'by_card';
    const SEARCH_BY_CODE = 'by_code';

    /**
     * @var \App\Services\Authentication\ClientAuthenticationManagerContract
     */
    private ClientAuthenticationManagerContract $managerContract;

    /**
     * @var \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface
     */
    private PromoDescriptionResolverInterface $promoDescriptionResolver;

    /**
     * @param \App\Services\Authentication\ClientAuthenticationManagerContract                   $managerContract
     * @param \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface $promoDescriptionResolver
     */
    public function __construct(
        ClientAuthenticationManagerContract $managerContract,
        PromoDescriptionResolverInterface   $promoDescriptionResolver
    )
    {
        $this->managerContract = $managerContract;
        $this->promoDescriptionResolver = $promoDescriptionResolver;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     * @throws \Throwable
     */
    public function handleDocument(Request $request): array
    {
        $body = $request->all();
        $validationClient = null;
        $client = null;
        try {
            $validationClient = $this->validateClientData($body);
            $client = $this->searchClient($validationClient);
        } catch (FrontolBadRequestException $e) {
        }

        $responseData = [];
        $action = Arr::get($body, 'action');
        switch ($action) {
            case 'calculate':
            case 'payByBonus':
                if (! $client) {
                    if ($this->isNotEmptyClientData($body)) {
                        $responseData['cashierInformation'] = [['text' => 'Клиент не найден']];
                    }

                    return $this->makeResponse($responseData, true);
                }

                $this->addClientDataIfExist($responseData, $client);
                $storeUuid = Arr::get($body, 'businessUnit');
                if (! $storeUuid) {
                    return $this->makeResponse($responseData);
                }

                $store = User::find($storeUuid);
                if (! $store) {
                    return $this->makeResponse($responseData);
                }

                $uuid = $this->getUid($body);
                if ($action === 'calculate') {
                    $loyaltyCard = $this->resolveClientCard($validationClient, $client, true);
                    $this->applyDiscountsAndBonus($uuid, $client, $store, $responseData, $body, $loyaltyCard ?: null);
                } elseif ($uuid) {
                    $this->applyBonusFor($uuid, $client, $store, $responseData, $body);
                }
                break;
            case 'cancelBonusPayment':
                break;
            case 'confirm':
                $storeUuid = Arr::get($body, 'businessUnit');
                if (! $storeUuid) {
                    return $this->makeResponse();
                }

                $store = User::find($storeUuid);
                if (! $store) {
                    return $this->makeResponse();
                }

                if ($validationClient) {
                    $loyaltyCard = $this->resolveClientCard($validationClient, $client, false);
                    if ($loyaltyCard === false) {
                        return $this->makeResponse();
                    }

                    $cardNumber = $loyaltyCard ? $loyaltyCard->number : null;
                } else {
                    $cardNumber = '';
                    $loyaltyCard = null;
                }

                $type = Arr::get($body, 'type');
                if ($type === 'refundReceipt') {
                    // Прошла оплата
                    $refundReferenceUuid = $body['referenceUid'];
                } else {
                    $refundReferenceUuid = null;
                }

                $this->createReceipt(
                    $body,
                    $storeUuid,
                    (string)$cardNumber,
                    $loyaltyCard,
                    $refundReferenceUuid
                );
                break;
        }

        return $this->makeResponse($responseData);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     * @throws \Throwable
     */
    public function handleExtraClient(Request $request): array
    {
        $body = $request->all();
        $action = Arr::get($body, 'action');
        $clientValidation = $this->validateClientData($body);
        if ($clientValidation[1] !== static::SEARCH_BY_PHONE) {
            // Главным должен быть телефон
            throw FrontolBadRequestException::make('Укажите телефон как идентификатор клиента');
        }

        $responseData = [];
        switch ($action) {
            case 'describe':
                $responseData['form'] = $this->makeFormForRegistration($clientValidation);
                break;
            case 'check':
                $validated = $this->validateClientRegistration($clientValidation, $request);

                $client = Client::firstOrCreate(['phone' => $validated['phone']]);
                $code = $this->managerContract->generateAuthenticationCode($client);
                $client->notify(AuthenticationCode::make($code));
                $responseData['client'] = [
                    'validationCode' => $code
                ];
                break;
            case 'execute':
                $validated = $this->validateClientRegistration($clientValidation, $request);
                $responseData = $this->registerClient($validated);
                break;
            default:
                throw FrontolBadRequestException::make('Некорректный запрос');
        }

        return $this->makeResponse($responseData);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function handleClient(Request $request): array
    {
        $body = $request->all();
        $client = $this->findClient($body);
        $return = [
            'cashierInformation' => [],
            'printingInformation' => []
        ];

        if (! $client) {
            $return['cashierInformation'][] = [
                'text' => 'Клиент не найдет'
            ];
        } else {
            $return['printingInformation'][] = [
                'text' => 'Клиент ' . $client->name
            ];
        }

        return $this->makeResponse($return);
    }

    /**
     * @param array $validationClient
     * @param Client|null $client
     * @param bool $validateCode
     *
     * @return LoyaltyCard|bool|null
     */
    protected function resolveClientCard(array $validationClient, ?Client $client, bool $validateCode)
    {
        if ($validationClient[1] === static::SEARCH_BY_CARD) {
            $cardNumber = $validationClient[0];
            return LoyaltyCard::where('number', $cardNumber)->first();
        }

        if ($validationClient[1] === static::SEARCH_BY_CODE) {
            $codeNumber = $validationClient[0];
            $loyaltyCodeQuery = LoyaltyCode::whereCode($codeNumber);
            if ($validateCode) {
                $loyaltyCodeQuery = $loyaltyCodeQuery->where('expires_on', '>', now());
            }

            /** @var LoyaltyCode $code */
            $code = $loyaltyCodeQuery->first();
            return $code?->client?->loyaltyCards()->first();
        }

        if (! $client) {
            return false;
        }

        $cards = $client->loyaltyCards;
        if ($cards->isEmpty()) {
            return false;
        }

        return $client->loyaltyCards->first();
    }

    /**
     * @param array $body
     *
     * @return bool
     */
    protected function isNotEmptyClientData(array $body): bool
    {
        $client = Arr::get($body, 'client', []);
        if (! $client) {
            return false;
        }

        $card = Arr::get($client, 'card');
        $phone = Arr::get($client, 'mobilePhone');
        return $card || $phone;
    }

    /**
     * @param array $clientData
     *
     * @return array
     * @throws \Throwable
     */
    protected function registerClient(array $clientData): array
    {
        $client = Client::where('phone', $clientData['phone'])->first();
        if ($client === null) {
            throw FrontolBadRequestException::make('Клиент не найден');
        }

        $card = LoyaltyCard::where('number', $clientData['card'])->first();
        if ($card === null) {
            throw FrontolBadRequestException::make('Карта не найдена');
        }

        if ($card->client && $card->client->uuid !== $client->uuid) {
            throw FrontolBadRequestException::make('Карта уже привязана к другому клиенту');
        }

        return DB::transaction(function () use ($card, $client) {
            $card->client()->associate($client);
            $card->saveOrFail();

            return ['cashierInformation' => [
                ['text' => 'Клиент зарегестрирован']
            ]];
        });
    }

    /**
     * @param array $data
     * @param bool  $addClientBlock
     *
     * @return array
     */
    protected function makeResponse(array $data = [], bool $addClientBlock = false): array
    {
        if (! array_key_exists('code', $data)) {
            $data['code'] = 0;
        }

        if ($addClientBlock && ! array_key_exists('client', $data)) {
            $data['client'] = [];
        }

        return $data;
    }

    /**
     * @param array $body
     *
     * @return \App\Models\Client|null
     */
    protected function findClient(array $body): ?Client
    {
        $validated = $this->validateClientData($body);
        return $this->searchClient($validated);
    }

    /**
     * @param array $validatedData
     *
     * @return \App\Models\Client|null
     */
    protected function searchClient(array $validatedData): ?Client
    {
        list($value, $type) = $validatedData;
        if ($type === static::SEARCH_BY_PHONE) {
            return Client::where('phone', $value)->first();
        }

        if ($type === static::SEARCH_BY_CODE) {
            /** @var LoyaltyCode $code */
            $code = LoyaltyCode::whereCode($value)
                ->where('expires_on', '>', now())
                ->first();
            return $code ? $code->client : null;
        }

        $card = LoyaltyCard::where('number', $value)->first();
        if (! $card) {
            throw FrontolBadRequestException::make('Указанная карта лояльности не найдена');
        }

        return $card->client;
    }

    /**
     * @param array $body
     *
     * @return array
     */
    protected function validateClientData(array $body): array
    {
        $client = Arr::get($body, 'client');
        if (! $client) {
            throw FrontolBadRequestException::make('Данные клиента не указаны');
        }

        $rules = $this->makeBaseClientRules();
        if (isset($client['mobilePhone'])) {
            try {
                $validator = $this->makeValidator($client, [
                    'mobilePhone' => $rules['phone']
                ]);
                $validator->validate();
            } catch (ValidationException $exception) {
                throw FrontolBadRequestException::make('Некорректно указан телефон клиента');
            }

            return [$client['mobilePhone'], static::SEARCH_BY_PHONE];
        } elseif (isset($client['card'])) {
            // Search by code
            if (Uuid::isValid($client['card'])) {
                return [$client['card'], static::SEARCH_BY_CODE];
            }

            if (config('app.integrations.frontol.useCode')) {
                throw FrontolBadRequestException::make('Некорректно указан код');
            }

            try {
                $validator = $this->makeValidator($client, [
                    'card' => $rules['card']
                ]);
                $validator->validate();
            } catch (ValidationException $exception) {
                throw FrontolBadRequestException::make('Некорректно указана карта клиента');
            }

            return [$client['card'], static::SEARCH_BY_CARD];
        } else {
            throw FrontolBadRequestException::make('Некорректно указаны данные клиента');
        }
    }

    /**
     * @param array                    $clientValidation
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function validateClientRegistration(array $clientValidation, Request $request): array
    {
        $data = [];
        $body = $request->all();
        $values = Arr::wrap(Arr::get($body, 'values', []));

        $rules = $this->makeBaseClientRules();
        if ($clientValidation[1] === static::SEARCH_BY_PHONE) {
            $data['phone'] = $clientValidation[0];
        } else {
            $data['card'] = $clientValidation[0];
        }

        foreach ($values as $row) {
            $data[Arr::get($row, 'name')] = Arr::get($row, 'value');
        }

        $validator = $this->makeValidator($data, $rules);
        try {
            return $validator->validate();
        } catch (ValidationException $exception) {
            $message = implode(';', $exception->validator->errors()->all());
            throw FrontolBadRequestException::make('Ошибка валидации: ' . $message);
        }
    }

    /**
     * @param array $clientValidation
     *
     * @return array
     */
    protected function makeFormForRegistration(array $clientValidation): array
    {
        $title = ['text' => 'Анкета клиента'];
        $elements = [];

        // ФИО
//        $elements[] = [
//            'type' => 'inputLine',
//            'name' => 'name',
//            'text' => 'ФИО клиента:',
//            'regExp' => '^\D{3,255}$',
//        ];

        if ($clientValidation[1] === static::SEARCH_BY_PHONE) {
            // Карта
            $elements[] = [
                'type' => 'inputLine',
                'name' => 'card',
                'text' => 'Карта клиента: ',
                'regExp' => '^\d{3,20}$',
            ];
        } else {
            // Телефон
            $elements[] = [
                'type' => 'inputLine',
                'name' => 'phone',
                'text' => 'Мобильный телефон:',
                'default' => '+7',
                'regExp' => '^\+7\d{10}$',
            ];
        }

        return compact('title', 'elements');
    }

    /**
     * @return array
     */
    protected function makeBaseClientRules(): array
    {
        return [
            'card' => 'required|digits_between:3,20',
            'phone' => ['required', new PhoneNumber()],
        ];
    }

    /**
     * @param array $data
     * @param array $rules
     *
     * @return \Illuminate\Validation\Validator
     */
    protected function makeValidator(array $data, array $rules): Validator
    {
        /** @var ValidationFactory $factory */
        $factory = app(ValidationFactory::class);
        /** @var \Illuminate\Validation\Validator $validator */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $validator = $factory->make($data, $rules);
        return $validator;
    }

    /**
     * @param array                   $data
     * @param \App\Models\Client|null $client
     */
    protected function addClientDataIfExist(array &$data, ?Client $client): void
    {
        if ($client) {
            $data['client'] = $this->makeClientData($client);
        }
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return array
     */
    protected function makeClientData(CLient $client): array
    {
        return [
            'mobilePhone' => $client->phone,
            'email' => $client->email,
        ];
    }

    /**
     * @param array                        $data
     * @param string                       $storeUuid
     * @param string|null                  $cardNumber
     * @param \App\Models\LoyaltyCard|null $loyaltyCard
     * @param string|null                  $refundReferenceUuid
     *
     * @throws \Throwable
     */
    protected function createReceipt(
        array        $data,
        string       $storeUuid,
        string       $cardNumber,
        ?LoyaltyCard $loyaltyCard = null,
        ?string      $refundReferenceUuid = null
    ): void
    {
        $receipt = new Receipt();
        $uuid = $this->getUid($data);
        if (Receipt::whereUuid($uuid)->exists()) {
            return;
        }

        $receipt->forceFill([
            'uuid' => $uuid,
            'user_uuid' => $storeUuid,
            'id' => $data['number'],
            'receipt_package_id' => $data['number'],
            'loyalty_card_number' => $cardNumber,
            'loyalty_card_uuid' => $loyaltyCard ? $loyaltyCard->uuid : null,
            'loyalty_card_type_uuid' => $loyaltyCard ? $loyaltyCard->loyalty_card_type_uuid : null,
            'refund_by_receipt_uuid' => $refundReferenceUuid,
            'total' => 0,
            'created_at' => Date::parse($data['dateTime']),
        ]);

        $payments = Arr::get($data, 'payments', []);
        $paidBonuses = 0;
        foreach ($payments as $payment) {
            if (isset($payment['type']) && $payment['type'] === 'bonus') {
                $paidBonuses = (int)$payment['amount'];
            }
        }

        $cachedData = [];
        if ($uuid) {
            $cachedData = $this->loadFromCache($uuid) ?: [];
        }

        DB::transaction(function () use ($data, $receipt, $refundReferenceUuid, $cachedData, $loyaltyCard, $paidBonuses) {
            $priceCollection = null;
            if ($cachedData) {
                /** @var CollectionPriceDataInterface $priceCollection */
                $priceCollection = $cachedData['collection'];
                if ($paidBonuses > 0) {
                    $priceCollection = $this->fixPriceCollectionWithBonuses($priceCollection, $paidBonuses);
                }

                $receipt->applyCollectionPriceData($priceCollection);
                if ($receipt->paid_bonus <= 0) {
                    $receipt->bonus_to_charge = $receipt->total_bonus;
                }
            }
            $receipt->save();

            $priceData = Arr::get($cachedData, 'price_data', []);
            $total = $this->createReceiptLines($data['positions'], $receipt, $priceData, $refundReferenceUuid);
//            $total -= $paidBonuses;
            if ($priceCollection) {
                if (! FloatHelper::isEqual($receipt->total, $total)) {
                    Log::channel('frontol-loyalty-system')
                        ->warning('The price from cache are not equal to price from frontol', [
                            'cache_data' => $priceCollection->toArray(),
                            'frontol_data' => $data
                        ]);

                    $receipt->total = $total;
                    $receipt->save();
                }
            } else {
                $receipt->total = $total;
                $receipt->save();
            }

            ReceiptReceived::dispatch($receipt);
            if ($loyaltyCard) {
                $client = $loyaltyCard->client;
                if ($client) {
                    $client->notify(FrontolReceiptReceived::create($receipt));
                }
            }
        });

        if ($cachedData) {
            Cache::forget($this->makeCacheKey($uuid));
        }
    }

    /**
     * @param \App\Services\Management\Client\Product\CollectionPriceDataInterface $priceCollection
     * @param int                                                                  $paidBonuses
     *
     * @return void
     */
    protected function fixPriceCollectionWithBonuses(CollectionPriceDataInterface $priceCollection, int $paidBonuses): CollectionPriceData
    {
        $total = $priceCollection->getTotalPriceWithDiscount();
        $newTotal = $total - $paidBonuses;

        return new CollectionPriceData([
            'total_discount' => $priceCollection->getTotalDiscount(),
            'total_price_with_discount' => $newTotal,
            'total_weight' => $priceCollection->getTotalWeight(),
            'total_quantity' => $priceCollection->getTotalQuantity(),
            'total_bonus' => $priceCollection->getTotalBonus(),
            'paid_bonus' => $paidBonuses,
        ]);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getUid(array $data): string
    {
        $uuid = (string)Arr::get($data, 'uid', '');
        if ($uuid && $uuid[0] == '{') {
            $uuid = trim($uuid, '{}');
        }

        return mb_strtolower($uuid);
    }

    /**
     * @param array               $positions
     * @param \App\Models\Receipt $receipt
     * @param array               $cachedData
     * @param string|null         $refundReferenceUuid
     *
     * @return float
     */
    protected function createReceiptLines(array $positions, Receipt $receipt, array $cachedData, ?string $refundReferenceUuid = null): float
    {
        $total = 0;
        foreach ($positions as $positionIndex => $position) {
            $total += $position['totalAmount'];
            if ($refundReferenceUuid) {
                $position['totalAmount'] = -$position['totalAmount'];
            }

            $receiptLine = new ReceiptLine();
            $receiptLine->forceFill([
                'barcode' => '',
                'price_with_discount' => $position['price'],
                'discount' => 0,
                'total' => $position['totalAmount'],
                'quantity' => $position['quantity'],
            ]);

            $assortment = $this->findAssortment($position['id']);
            if ($assortment) {
                $receiptLine->assortment_uuid = $assortment->uuid;
                $product = $this->findProduct($receipt->user_uuid, $receiptLine->assortment_uuid, true);
                $receiptLine->product_uuid = $product ? $product->uuid : null;

                /** @var ?PriceDataInterface $priceData */
                $priceData = Arr::get($cachedData, $positionIndex);
                if ($priceData) {
                    $receiptLine->applyPriceData($priceData);
                }

                if ($assortment->barcode) {
                    $receiptLine->barcode = $assortment->barcode;
                }
            }

            $receiptLine->receipt()->associate($receipt);
            $receiptLine->save();
        }

        return $total;
    }

    /**
     * @param string $article
     *
     * @return \App\Models\Assortment|null
     */
    protected function findAssortment(string $article): ?Assortment
    {
        try {
            $first = Assortment::where('assortments.article', $article)
                ->first();
        } catch (QueryException $e) {
            $first = null;
        }

        return $first;
    }

    /**
     * @param string $storeUuid
     * @param string $assortmentUuid
     * @param bool   $selectOnlyId
     *
     * @return \App\Models\Product|null
     */
    protected function findProduct(string $storeUuid, string $assortmentUuid, bool $selectOnlyId = false): ?Product
    {
        try {
            /** @var Product $product */
            $product = Product::select($selectOnlyId ? 'products.uuid' : '*')
                ->where('products.assortment_uuid', $assortmentUuid)
                ->where('products.user_uuid', $storeUuid)
                ->first();
            return $product;
        } catch (QueryException $e) {
            return null;
        }
    }

    /**
     * @param string|null                  $uuid
     * @param \App\Models\Client           $client
     * @param \App\Models\User             $store
     * @param array                        $responseData
     * @param array                        $data
     * @param \App\Models\LoyaltyCard|null $loyaltyCard
     *
     * @return void
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function applyDiscountsAndBonus(?string $uuid, Client $client, User $store, array &$responseData, array $data, ?LoyaltyCard $loyaltyCard): void
    {
        $result = $this->calculateDiscountAndBonuses(
            $client,
            $store,
            $data['positions']
        );

        /**
         * @var CollectionPriceDataInterface      $calculatedResult
         * @var array<string, PriceDataInterface> $priceDataMap
         * @var array                             $printData
         * @var array                             $positionsDiscounts
         * @var array                             $nonProcessedPositions
         */
        list($calculatedResult, $priceDataMap, $printData, $positionsDiscounts, $nonProcessedPositions) = $result;

        $printNonProcessedRows = [];
        if ($nonProcessedPositions) {
            foreach ($nonProcessedPositions as $nonProcessedPosition) {
                $printRows = $this->makePrintingInfoForPosition($nonProcessedPosition);
                $printNonProcessedRows = array_merge($printNonProcessedRows, $printRows);
            }
        }

        if (! $calculatedResult) {
            $responseData['printingInformation'] = [
                $printNonProcessedRows
            ];

            return;
        }

        if ($uuid) {
            $this->saveIntoCache($uuid, $calculatedResult, $priceDataMap);
        }

        /** @var MaxBonusesCalculatorInterface $bonusesCalculator */
        $bonusesCalculator = app(MaxBonusesCalculatorInterface::class);
        $maxBonusesToPay = $bonusesCalculator->calculate($calculatedResult->getTotalPriceWithDiscount());
        $clientBalance = $client->bonus_balance;
        if ($maxBonusesToPay > $clientBalance) {
            $maxBonusesToPay = $clientBalance;
        }

        $printBalances = $this->makeBalancePrintInfo($client, $calculatedResult, $loyaltyCard);
        $responseData['client']['availableAmount'] = max($maxBonusesToPay, 0);
        if ($printData) {
            $printData = array_merge($printData, $printNonProcessedRows);
            $responseData['printingInformation'] = [
                // Один дополнительный слип
                $printData,
                $printBalances
            ];
        } else {
            $responseData['printingInformation'] = [
                $printBalances
            ];
        }

        if ($positionsDiscounts) {
            $responseData['document'] = [
                'positions' => $positionsDiscounts
            ];
        }
    }

    /**
     * @param \App\Models\Client                                                   $client
     * @param \App\Services\Management\Client\Product\CollectionPriceDataInterface $data
     * @param \App\Models\LoyaltyCard|null                                         $loyaltyCard
     *
     * @return array
     */
    protected function makeBalancePrintInfo(Client $client, CollectionPriceDataInterface $data, ?LoyaltyCard $loyaltyCard): array
    {
        // Печать фраз про баланс/карту и начисление
        $result = [];
        if ($loyaltyCard) {
            $result[] = [
                'type' => 'text',
                'text' => "Карта клиента: $loyaltyCard->number"
            ];
        }

        $result[] = [
            'type' => 'text',
            'text' => "Бонусный баланс: $client->bonus_balance"
        ];

        $bonus = $data->getTotalBonus();
        $result[] = [
            'type' => 'text',
            'text' => "Сколько начислиться: $bonus"
        ];

        return $result;
    }

    /**
     * @param string                                                               $uuid
     * @param \App\Services\Management\Client\Product\CollectionPriceDataInterface $calculatedResult
     * @param array<string, PriceDataInterface>                                    $priceDataMap
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function saveIntoCache(string $uuid, CollectionPriceDataInterface $calculatedResult, array $priceDataMap): void
    {
        $data = [
            'collection' => $calculatedResult,
            'price_data' => $priceDataMap
        ];

        $ttl = config('app.receipt.discount.frontol_cache_ttl');
        $key = $this->makeCacheKey($uuid);
        Cache::set($key, $data, $ttl);
    }

    /**
     * @param string $uuid
     *
     * @return array
     */
    protected function loadFromCache(string $uuid): ?array
    {
        $key = $this->makeCacheKey($uuid);
        $data = Cache::get($key);
        if (! $data) {
            return null;
        }

        if (! isset($data['collection']) || ! isset($data['price_data'])) {
            return null;
        }

        return $data;
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\User   $store
     * @param array              $positions
     *
     * @return void
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function calculateDiscountAndBonuses(Client $client, User $store, array $positions): ?array
    {
        list($positionsByArticle, $priceItems, $nonProcessedPositions) = $this->makePriceData($store->uuid, $positions);
        if (! $priceItems) {
            return [null, null, null, null, $nonProcessedPositions];
        }

        /** @var ClientProductCollectionPriceCalculatorInterface $calculator */
        $calculator = app(ClientProductCollectionPriceCalculatorInterface::class);
        $priceDataMap = [];
        $printData = [];
        $positionsDiscounts = [];

        $closure = function ($positionIndex, ProductItem $productItem, PriceDataInterface $data) use ($positionsByArticle, &$positionsDiscounts, &$printData, &$priceDataMap) {
            $priceDataMap[$positionIndex] = $data;
            $positions = $positionsByArticle[$productItem->getProduct()->assortment->article];
            $position = $positions[$positionIndex];

            $printRows = $this->makePrintingInfoForPosition(
                $position,
                $data
            );
            $printData = array_merge($printData, $printRows);

            $discount = $data->getDiscountModel();
            if ($discount && $discount->getKey() !== PromoDescription::VIRTUAL_FRONTOL_DISCOUNT_UUID) {
                $positionsDiscounts[] = [
                    'index' => $position['index'],
                    'discountAmount' => $data->getTotalDiscount(),
                ];
            }
        };

        /** @var FrontolInMemoryDiscount $inMemoryDiscount */
        $inMemoryDiscount = app(FrontolInMemoryDiscount::class);
        $inMemoryDiscount->setPositions($positions);

        $ctx = new CalculateContext(
            $client,
            TargetEnum::RECEIPT
        );
        $calculatedResult = $calculator->calculate($ctx, $priceItems, $closure);
        return [$calculatedResult, $priceDataMap, $printData, $positionsDiscounts, $nonProcessedPositions];
    }

    /**
     * @param string             $uuid
     * @param \App\Models\Client $client
     * @param \App\Models\User   $store
     * @param array              $responseData
     * @param array              $data
     *
     * @return void
     */
    protected function applyBonusFor(string $uuid, Client $client, User $store, array& $responseData, array $data)
    {
        $bonusAmount = Arr::get($data, 'payment.amount');
        if (! $bonusAmount || $bonusAmount <= 0) {
            return;
        }

        $positions = $data['positions'];
        list(, $priceItems, ) = $this->makePriceData($store->uuid, $positions);
        if (! $priceItems) {
            return;
        }

        $cachedData = $this->loadFromCache($uuid);
        if (! $cachedData) {
            return;
        }

        /** @var CollectionPriceDataInterface $totalData */
        $totalData = $cachedData['collection'];
        $maxBonusesCalc = app(MaxBonusesCalculatorInterface::class);
        $max = $maxBonusesCalc->calculate($totalData->getTotalPriceWithDiscount());
        if ($max < $bonusAmount) {
            throw new FrontolBadRequestException('Указано слишком много бонусов к списанию');
        } elseif ($bonusAmount > $client->bonus_balance) {
            throw new FrontolBadRequestException('У клиента недостаточно бонусов на балансе');
        }

        // Печать фразы про списание
        $toPrint = [[
            'type' => 'text',
            'text' => "Сколько списано: $bonusAmount"
        ]];
        $responseData['printingInformation'] = [
            $toPrint
        ];
    }

    /**
     * @param string $storeUuid
     * @param array  $positions
     *
     * @return array{0: array<string, array<int, array>>, 1: ProductItem[], 2: array<int, array>}
     */
    protected function makePriceData(string $storeUuid, array $positions): array
    {
        $articles = [];
        $positionsByArticle = [];
        $nonProcessedPositions = [];
        foreach ($positions as $positionIndex => $position) {
            $article = $position['id'];
            $articles[$article] = $article;
            $positionsByArticle[$article][$positionIndex] = $position;
            $nonProcessedPositions[$positionIndex] = $position;
        }

        /** @var Assortment[]|\Illuminate\Database\Eloquent\Collection $assortments */
        $assortments = Assortment::whereIn('article', $articles)->get();
        if ($assortments->isEmpty()) {
            return [[], [], $nonProcessedPositions];
        }

        $assortmentsByArticle = [];
        $assortmentUuids = [];
        foreach ($assortments as $assortment) {
            $uuid = $assortment->uuid;
            $assortmentUuids[] = $uuid;
            $assortmentsByArticle[$assortment->article] = $assortment;
        }

        $products = Product::where('user_uuid', $storeUuid)
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->get()
            ->keyBy('assortment_uuid');

        if ($products->isEmpty()) {
            return [[], [], $nonProcessedPositions];
        }

        $result = [];
        foreach ($positions as $positionIndex => $position) {
            $article = $position['id'];
            if (! isset($assortmentsByArticle[$article])) {
                continue;
            }

            $assortment = $assortmentsByArticle[$article];
            if (! isset($products[$assortment->uuid])) {
                continue;
            }

            $product = $products[$assortment->uuid];
            $positions = $positionsByArticle[$assortment->article];
            $needClone =  count($positions) > 1;

            if ($needClone) {
                $newProduct = clone $product;
            } else {
                $newProduct = $product;
            }

            $newProduct->setRelation('assortment', $assortment);
            $newProduct->price = $position['price'];
            $newProduct->loyaltySystemIndexInCheck = $positionIndex;

            $result[$positionIndex] = new ProductItem($newProduct, $position['quantity']);
            unset($nonProcessedPositions[$positionIndex]);
        }

        return [$positionsByArticle, $result, $nonProcessedPositions];
    }

    /**
     * @param array                                                           $position
     * @param \App\Services\Management\Client\Product\PriceDataInterface|null $priceData
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function makePrintingInfoForPosition(array $position, ?PriceDataInterface $priceData = null)
    {
        $result = [];
        $productName = $position['text'];
        $quantity = $position['quantity'];
        $totalAmount = $position['totalAmount'];
        $originalPrice = (float)$position['price'];

        $discount = $priceData ? $priceData->getDiscountModel() : null;
        if ($discount) {
            $receiptLineFake = new ReceiptLine();
            $receiptLineFake->discountable()->associate($discount);
            $discountableType = $receiptLineFake->discountable_type;

            $discountPrice = $priceData->getPriceWithDiscount();
            $discountInfo = $this->promoDescriptionResolver->resolve($discountableType);
            $discountPercent = $this->resolveDiscountPercent($discount, $originalPrice);
        } else {
            $discountPrice = null;
            $discountInfo = null;
            $discountPercent = null;
        }

        if (! $discountInfo || ! $discountPercent) {
            $result[] = [
                'type' => 'pair',
                'left' => $productName,
                'right' => "$quantity * $originalPrice ≡ $totalAmount"
            ];
            return $result;
        }

        $result[] = [
            'type' => 'pair',
            'left' => $productName,
            'right' => "$quantity * $discountPrice ≡ $totalAmount"
        ];
        $result[] = [
            'type' => 'text',
            'text' => "$discountInfo->name"
        ];

        return $result;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param float                               $originalPrice
     *
     * @return string|null
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function resolveDiscountPercent(Model $model, float $originalPrice): ?string
    {
        $class = get_class($model);
        switch ($class) {
            case PromoDiverseFoodClientDiscount::class:
                /** @var PromoDiverseFoodClientDiscount $model */
                $percent = $model->discount_percent;
                break;
            case ClientActivePromoFavoriteAssortment::class:
                /** @var ClientActivePromoFavoriteAssortment $model */
                $percent = $model->discount_percent;
                break;
            case ClientPromotion::class:
                /** @var ClientPromotion $model */
                $percent = $model->discount_percent;
                break;
            case PromoYellowPrice::class:
                /** @var PromoYellowPrice $model */
                $percent = MoneyHelper::of($originalPrice)
                    ->minus($model->price)
                    ->dividedBy($originalPrice)
                    ->multipliedBy(100);
                $percent = MoneyHelper::toFloat($percent);
                break;
            default:
                return null;
        }

        $formatted = number_format($percent, 2);
        return trim($formatted, "0.");
    }

    /**
     * @param string $uuid
     *
     * @return string
     */
    protected function makeCacheKey(string $uuid): string
    {
        return 'frontol_receipt_discount:' . $uuid;
    }
}
