<?php

namespace App\Console\Commands;

use App\Models\AssortmentUnit;
use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\Integrations\Atol\AtolOnlineClient;
use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorInterface;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\MoneyHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Ramsey\Uuid\Uuid;

/**
 * Пример использования
 * php artisan payments:test-atol-2 997f03e4-315c-4913-838c-ce2bc116f435 --mode=2
 */
class MakeTestPaymentAtol2Staged extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:test-atol-2 {uuid : Client card uuid} 
          {--max-price=10 : The sum of test payment}
          {--mode=0 : Test payment mode}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестовый платеж (с ATOL) с двух-стадийной оплатой';

    /**
     * @var AcquireInterface
     */
    protected $acquireHandler;

    /**
     * @var AtolOnlineClient
     */
    protected $atolOnlineClient;

    /**
     * Execute the console command.
     */
    public function handle(AcquireInterface $acquireHandler, AtolOnlineClient $atolOnlineClient)
    {
        $this->acquireHandler = $acquireHandler;
        $this->atolOnlineClient = $atolOnlineClient;

        $uuid = $this->input->getArgument('uuid');
        if (! Uuid::isValid($uuid)) {
            $this->error('Некорректный uuid');
            return;
        }

        /** @var ClientCreditCard $clientCard */
        $clientCard = ClientCreditCard::find($uuid);
        if (! $clientCard) {
            $this->error('Карта клиента не найдена');
            return;
        }

        $maxPrice = (int)$this->input->getOption('max-price');
        if ($maxPrice <= 0) {
            $this->error('Указана некорректная сумма');
            return;
        }

        $testProduct = Product::query()
            ->where('products.price', '<', $maxPrice)
            ->where('products.price', '>=', 1)
            ->join('assortments', 'assortments.uuid', 'products.assortment_uuid')
            ->where('assortments.assortment_unit_id', AssortmentUnit::ID_PIECE)
            ->orderBy('products.price')
            ->first();

        if (! $testProduct) {
            $this->error('Тестовый продукт не найден');
            return;
        }

        $assortment = $testProduct->assortment;
        $this->line("Выбранный продукт: $assortment->name, с ценой: $testProduct->price");

        $orderUuid = Uuid::uuid4();
        $targetNumber = $orderUuid->getHex()->toString();
        $this->line("Айди тестового заказа: $targetNumber");

        $totalPrice = MoneyHelper::toKopek($testProduct->price * 2);

        $testProduct->loadMissing('assortment');
        $orderProduct = new OrderProduct();
        $orderProduct->quantity = 2;
        $orderProduct->price_with_discount = $testProduct->price;
        $orderProduct->total_amount_with_discount = $totalPrice / 100;
        $orderProduct->paid_bonus = null;


        $orderProduct->setRelation('product', $testProduct);
        $order = new Order();
        $order->uuid = $targetNumber;
        $order->total_price = $totalPrice / 100;
        $order->delivery_price = null;
        $order->created_at = Date::now();
        $order->setRelation('orderProducts', new Collection([$orderProduct]));
        $order->setRelation('client', $clientCard->client);
        $order->setRelation('store', $testProduct->user);

        $mode = (int)$this->input->getOption('mode');
        switch ($mode) {
            case 0:
                // Common payment
                $this->info('Сценарий 0: Закрытие чека без изменения цены');
                break;
            case 1:
                $this->info('Сценарий 1: Закрытие чека с измененим цены в меньшую сторону (и возврат через reverse)');
                break;
            case 11:
                $this->info('Сценарий 1-1: Закрытие чека с измененим цены в меньшую сторону (и возврат через refund)');
                break;
            case 2:
                $this->info('Сценарий 2: Закрытие чека с измененим цены в большую сторону');
                break;
            case 3:
                $this->info('Сценарий 3: Отмена захолдированного платежа (reverse)');
                break;
            case 4:
                $this->info('Сценарий 4: Отмена захолдированного платежа (refund)');
                break;
            default:
                $this->error('Выбран некорректный сценарий');
                return;
        }

        $this->line("Создаем холдированный платеж с 2 единицами продукта. Итоговая цена: $totalPrice");
        $orderPaymentId = $this->generateSimplePayment($clientCard->client, $targetNumber, $totalPrice, $clientCard->binding_id);
        if ($orderPaymentId === false) {
            return;
        }
        $this->line("Отправляем предварительный чек в atol");
        if (! $this->sendAtolSell($order, true)) {
            return;
        }

        $order->uuid = $order->uuid . '/f';
        switch ($mode) {
            case 0:
                // Common payment
                $this->line("Депозитим на ровно ту же сумму");
                if (! $this->deposit($orderPaymentId, $totalPrice)) {
                    return;
                }

                $this->line("Отправляем финальный чек в atol");
                if (! $this->sendAtolSell($order)) {
                    return;
                }
                $this->info("Сценарий 0 завершен успешно. Заказ проведен без изменения цены");
                break;
            case 1:
            case 11:
                // Lower price: Remove one element
                $newTotalPrice = MoneyHelper::toKopek($testProduct->price);
                $order->total_price = $newTotalPrice / 100;
                $orderProduct->quantity = 1;
                $orderProduct->total_amount_with_discount = $newTotalPrice / 100;
                $this->line("Депозитим на сумму меньше. Новая итоговая цена: $newTotalPrice");
                if (! $this->deposit($orderPaymentId, $newTotalPrice)) {
                    return;
                }

                if ($mode === 1) {
                    $this->line("Возвращаем оставшееся через reverse. Возврат суммы $newTotalPrice");
                    if (! $this->reverse($orderPaymentId, $newTotalPrice)) {
                        return;
                    }
                } else {
                    $this->line("Возвращаем оставшееся через refund. Возврат суммы $newTotalPrice");
                    if (! $this->refund($orderPaymentId, $newTotalPrice)) {
                        return;
                    }
                }

                $this->line("Отправляем финальный чек после рефанда в atol");
                if (! $this->sendAtolSell($order)) {
                    return;
                }
                $this->info("Сценарий 1 завершен успешно. Заказ проведен с изменением цены в меньшую сторону");
                break;
            case 2:
                // Higher price
                $newTotalPrice = MoneyHelper::toKopek($testProduct->price * 3);
                $order->total_price = $newTotalPrice / 100;
                $orderProduct->total_amount_with_discount = $newTotalPrice / 100;
                $diffPrice = MoneyHelper::toKopek($testProduct->price);
                $this->line("Депозитим на сумму заказа (на ту что заходлировали), а итоговая новая будет $newTotalPrice");
                if (! $this->deposit($orderPaymentId, $totalPrice)) {
                    return;
                }

                $newTargetNumber = Uuid::uuid4()->getHex()->toString();
                $this->line("Создаем обычный платёж на разницу ($diffPrice). Новый номер $newTargetNumber.");
                if (! $this->generateSimplePayment($clientCard->client, $newTargetNumber, $diffPrice, $clientCard->binding_id, false)) {
                    return;
                }

                $orderProduct->quantity = 3;
                $this->line('');

                $this->line("Отправляем финальный чек после увеличения цены в atol");
                if (! $this->sendAtolSell($order)) {
                    return;
                }
                $this->info("Сценарий 2 завершен успешно. Заказ проведен с изменением цены в большую сторону");
                break;
            case 3:
                // Cancel payment
                if (! $this->reverse($orderPaymentId)) {
                    return;
                }

                $this->info("Сценарий 3 завершен успешно. Платеж отменен");
                break;
            case 4:
                // Cancel payment
                if (! $this->refund($orderPaymentId, $totalPrice)) {
                    return;
                }

                $this->info("Сценарий 4 завершен успешно. Платеж отменен");
                break;
        }
    }

    /**
     * @param \App\Models\Client $client
     * @param string             $targetNumber
     * @param int                $price
     * @param string             $bindingId
     *
     * @return mixed
     * @throws \Voronkovich\SberbankAcquiring\Exception\SberbankAcquiringException
     */
    protected function generateSimplePayment(Client $client, $targetNumber, $price, $bindingId, $isHold = true)
    {
        $this->line('Создаем платеж...');
        try {
            $result = $this->acquireHandler->registerAutoPayment(
                $bindingId,
                $client->uuid,
                $targetNumber,
                $price,
                route('web.success-payment'),
                route('web.error-payment'),
                $isHold
            );
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при создании платежа: ');
            $this->error($exception);
            return false;
        }

        $orderId = $result->id;
        $this->info("Платеж создан, его айди: $orderId");
        $this->line('Проводим платеж...');

        try {
            $bindResultErr = $this->acquireHandler->paymentOrderBinding(
                $orderId,
                $bindingId
            );
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при проведении платежа: ');
            $this->error($exception);
            return false;
        }

        if ($bindResultErr) {
            $errorMessage = 'Ошибка при проведении платежа: ' . $bindResultErr;
            $this->error($errorMessage);
            return false;
        }

        $this->info("Платёж успешно проведен");
        return $orderId;
    }

    protected function sendAtolSell(Order $order, bool $isAdvance = false)
    {
        $this->line('Закрываем чек в atol...');

        /** @var AtolSellRequestGeneratorInterface $generator */
        $generator = app(AtolSellRequestGeneratorInterface::class);
        $requestData = $generator->generate($order, $isAdvance);
        $this->info('Полученный запрос: ' . json_encode($requestData));
        try {
            $this->atolOnlineClient->sell($requestData);
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при закрытии чека в atol: ');
            $this->error($exception);
            return false;
        }

        return true;
    }

    protected function refund($orderPaymentId, $amount)
    {
        $this->line('Возвращаем часть цены...');
        try {
            $this->acquireHandler->refund($orderPaymentId, $amount);
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при возвращении части суммы: ');
            $this->error($exception);
            return false;
        }

        return true;
    }

    protected function reverse($orderPaymentId, $amount = null)
    {
        $this->line($amount ? 'Отменяем холдирование на часть суммы' : 'Отменяем холдирование');
        try {
            $this->acquireHandler->reverse($orderPaymentId, $amount);
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при отмене холдирования: ');
            $this->error($exception);
            return false;
        }

        return true;
    }

    protected function deposit($orderPaymentId, $amount)
    {
        $this->line('Депозитим на некоторую сумму...');
        try {
            $this->acquireHandler->deposit($orderPaymentId, $amount);
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при возвращении части суммы: ');
            $this->error($exception);
            return false;
        }

        return true;
    }
}
