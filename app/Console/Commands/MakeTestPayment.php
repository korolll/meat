<?php

namespace App\Console\Commands;

use App\Models\AssortmentUnit;
use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentVendor;
use App\Models\Product;
use App\Services\Management\Client\Order\Payment\PaymentOrderBundleGeneratorInterface;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use App\Services\Money\MoneyHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Ramsey\Uuid\Uuid;

/**
 * Пример использования
 * php artisan payments:test 997f03e4-315c-4913-838c-ce2bc116f435 --mode=2
 */
class MakeTestPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:test {uuid : Client card uuid} 
          {--max-price=10 : The sum of test payment}
          {--mode=0 : Test payment mode}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестовый платеж';

    /**
     * @var AcquireInterface
     */
    protected $acquireHandler;

    /**
     * Execute the console command.
     */
    public function handle(AcquireResolverInterface $resolver)
    {
        $this->acquireHandler = $resolver->resolveDefaultByVendor();
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

        $targetNumber = Uuid::uuid4()->getHex()->toString();
        $this->line("Айди тестового заказа: $targetNumber");

        $testProduct->loadMissing('assortment');
        $orderProduct = new OrderProduct();
        $orderProduct->quantity = 2;
        $orderProduct->price_with_discount = $testProduct->price;
        $orderProduct->paid_bonus = null;

        $totalPrice = MoneyHelper::toKopek($testProduct->price * 2);
        $orderProduct->setRelation('product', $testProduct);
        $order = new Order();
        $order->delivery_price = null;
        $order->created_at = Date::now();
        $order->setRelation('orderProducts', new Collection([$orderProduct]));

        $mode = (int)$this->input->getOption('mode');
        switch ($mode) {
            case 0:
                // Common payment
                $this->info('Сценарий 0: Закрытие чека без изменения цены');
                break;
            case 1:
                $this->info('Сценарий 1: Закрытие чека с измененим цены в меньшую сторону');
                break;
            case 2:
                $this->info('Сценарий 2: Закрытие чека с измененим цены в большую сторону');
                break;
            default:
                $this->error('Выбран некорректный сценарий');
                return;
        }

        $this->line("Создаем платеж с 2 единицами продукта. Итоговая цена: $totalPrice");
        $orderPaymentId = $this->generateSimplePayment($clientCard->client, $targetNumber, $totalPrice, $clientCard->binding_id);
        if ($orderPaymentId === false) {
            return;
        }

        switch ($mode) {
            case 0:
                // Common payment
                if (! $this->closePayment($targetNumber, $order, $clientCard->client, $totalPrice)) {
                    return;
                }
                $this->info("Сценарий 0 завершен успешно. Заказ проведен без изменения цены");
                break;
            case 1:
                // Lower price: Remove one element
                $newTotalPrice = MoneyHelper::toKopek($testProduct->price);
                $orderProduct->quantity = 1;
                $this->line("Делаем рефанд на сумму одной позиции. Потом закроем заказ. Новая итоговая цена: $newTotalPrice");

                $this->refund($targetNumber, $totalPrice);
                if (! $this->closePayment($targetNumber, $order, $clientCard->client, $newTotalPrice)) {
                    return;
                }
                $this->info("Сценарий 1 завершен успешно. Заказ проведен с изменением цены в меньшую сторону");
                break;
            case 2:
                // Higher price
                $newTotalPrice = MoneyHelper::toKopek($testProduct->price * 3);
                $diffPrice = MoneyHelper::toKopek($testProduct->price);
                $this->line("Создаем новый платеж на сумму одной позиции (сумма $diffPrice). Новая итоговая цена: $newTotalPrice. Затем закроем новый заказ с корзиной первого");
                $this->line("Затем закроем новый заказ с новой итоговой корзиной");

                $orderProduct->quantity = 3;
                $this->line('');
                $newTargetNumber = Uuid::uuid4()->getHex()->toString();
                $this->line("Создаем новый платёж на разницу. Новый номер $newTargetNumber.");
                $additionalOrderPaymentId = $this->generateSimplePayment($clientCard->client, $newTargetNumber, $diffPrice, $clientCard->binding_id);
                if ($additionalOrderPaymentId === false) {
                    return;
                }

                if (! $this->closePayment($newTargetNumber, $order, $clientCard->client, $newTotalPrice)) {
                    return;
                }
                $this->info("Сценарий 1 завершен успешно. Заказ проведен с изменением цены в большую сторону");
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
    protected function generateSimplePayment(Client $client, $targetNumber, $price, $bindingId)
    {
        $this->line('Создаем платеж...');
        try {
            $result = $this->acquireHandler->registerAutoPayment(
                $bindingId,
                $client->uuid,
                $targetNumber,
                $price,
                route('web.success-payment'),
                route('web.error-payment')
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

    protected function closePayment($orderPaymentId, Order $order, Client $client, $amount)
    {
        $this->line('Закрываем чек...');

        /** @var PaymentOrderBundleGeneratorInterface $generator */
        $generator = app(PaymentOrderBundleGeneratorInterface::class);
        $orderBundle = $generator->generate($client, $order);
        $this->info('Полученный orderBundle: ' . json_encode($orderBundle));
        try {
            $this->acquireHandler->closeOfdReceipt(
                $orderPaymentId,
                $amount,
                ['orderBundle' => $orderBundle]
            );
        } catch (\Throwable $exception) {
            $this->error('Возникла ошибка при закрытии чека: ');
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
}
