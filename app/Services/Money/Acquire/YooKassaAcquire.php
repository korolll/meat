<?php

namespace App\Services\Money\Acquire;

use App\Models\PaymentVendor;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use Illuminate\Support\Arr;
use YooKassa\Client;
use YooKassa\Model\CurrencyCode;
use YooKassa\Model\Metadata;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Model\Payment\PaymentMethod\PaymentMethodBankCard;
use YooKassa\Model\Payment\PaymentStatus;
use YooKassa\Request\Payments\ConfirmationAttributes\ConfirmationAttributesRedirect;
use YooKassa\Request\Payments\CreateCaptureRequest;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Request\Payments\CreatePaymentRequestInterface;
use YooKassa\Request\Payments\CreatePaymentResponse;
use YooKassa\Request\Refunds\CreateRefundRequest;


class YooKassaAcquire implements AcquireInterface
{
    protected Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client();
        $validConfig = static::getValidConfig($config);
        if (!$validConfig) {
            throw new \InvalidArgumentException('Invalid config');
        }

        if ($validConfig['authToken'] ?? '') {
            $this->client->setAuthToken($validConfig['authToken']);
        } else {
            $this->client->setAuth($validConfig['login'], $validConfig['password']);
        }
    }

    public static function getValidConfig(array $config): ?array
    {
        $token = Arr::get($config, 'authToken');
        if ($token && is_string($token)) {
            return ['authToken' => $token];
        }

        $login = Arr::get($config, 'login');
        $password = Arr::get($config, 'password');
        if ($login && is_string($login) && $password && is_string($password)) {
            return [
                'login' => $login,
                'password' => $password,
            ];
        }

        return null;
    }

    public function getVendorId(): string
    {
        return PaymentVendor::ID_YOOKASSA;
    }

    public function isConfirmationNeeded(PaymentStatusDto $dto): bool
    {
        return false;
    }

    public function getPaymentStatus(string $paymentId): PaymentStatusDto
    {
        $result = $this->client->getPaymentInfo($paymentId);
        return $this->makeStatusDto($result);
    }

    public function registerPaymentForBinding(string $clientId, string $orderNumber, int $amount, string $returnUrl, string $failUrl): CreatedPaymentDto
    {
        $paymentData = $this->buildPaymentData(
            $clientId,
            $orderNumber,
            $amount,
            $returnUrl,
            true,
            true
        );

        // Let user pick payment method
//        $paymentMethod = new PaymentDataBankCard();
//        $paymentData->setPaymentMethodData($paymentMethod);

        return $this->createPayment($paymentData);
    }

    public function registerAutoPayment(
        string $bindingId,
        string $clientId,
        string $orderNumber,
        int $amount,
        string $returnUrl,
        string $failUrl,
        bool $isHold = false
    ): CreatedPaymentDto
    {
        $paymentData = $this->buildPaymentData(
            $clientId,
            $orderNumber,
            $amount,
            null,
            !$isHold
        );

        $paymentData->setPaymentMethodId($bindingId);
        return $this->createPayment($paymentData);
    }

    protected function createPayment(CreatePaymentRequestInterface $request): CreatedPaymentDto
    {
        $result = $this->client->createPayment($request);
        return $this->makeCreatedPaymentResult($result);
    }

    public function refund(string $paymentId, int $amount): void
    {
        $builder = CreateRefundRequest::builder();
        $builder
            ->setAmount($this->makeAmount($amount))
            ->setPaymentId($paymentId);

        $this->client->createRefund($builder->build());
    }

    public function reverse(string $paymentId): void
    {
        $this->client->cancelPayment($paymentId);
    }

    public function deposit(string $paymentId, int $amount): void
    {
        $builder = CreateCaptureRequest::builder();
        $builder
            ->setAmount($this->makeAmount($amount));

        $this->client->capturePayment($builder->build(), $paymentId);
    }

    public function paymentOrderBinding(string $orderId, string $bindingId, array $data = []): ?string
    {
        throw new \Exception('Not implemented');
    }

    public function closeOfdReceipt(string $orderNumber, int $amount, array $data = []): void
    {
        throw new \Exception('Not implemented');
    }

    public function cancelBinding(string $bindingId): void
    {
        throw new \Exception('Not implemented');
    }

    protected function buildPaymentData(
        string $clientId,
        string $orderNumber,
        int $amount,
        ?string $returnUrl,
        bool $capture = true,
        bool $savePaymentMethod = false
    ): CreatePaymentRequest
    {
        $amountObj = $this->makeAmount($amount);
        $metadata = new Metadata([
            'order_number' => $orderNumber,
            'client_id' => $clientId
        ]);

        $paymentData = new CreatePaymentRequest();
        $paymentData
            ->setAmount($amountObj)
            ->setCapture($capture)
            ->setMetadata($metadata)
            ->setDescription('Заказ №' . $orderNumber)
            ->setSavePaymentMethod($savePaymentMethod);

        if ($returnUrl) {
            $confirmation = new ConfirmationAttributesRedirect();
            $confirmation->setReturnUrl($returnUrl);

            $paymentData->setConfirmation($confirmation);
        }

        // DEBUG BLOCK START
//        $receipt = new Receipt();
//        $list = new ListObject(ReceiptItem::class);
//        $item = new ReceiptItem();
//        $item->setDescription('TEST');
//        $item->setVatCode(1);
//        $item->setPrice(new ReceiptItemAmount($amount / 100, CurrencyCode::RUB));
//        $item->setQuantity(1);
//        $list->add($item);
//        $receipt->setItems($list);
//        $receipt->setCustomer([
//            'email' => 'kik-esozefu42@outlook.com'
//        ]);
//        $paymentData->setReceipt($receipt);
        // DEBUG BLOCK END

        return $paymentData;
    }

    protected function makeAmount(int $amountKopek): MonetaryAmount
    {
        return new MonetaryAmount($amountKopek / 100, CurrencyCode::RUB);
    }

    protected function makeStatusDto(PaymentInterface $result): PaymentStatusDto
    {
        $paymentMethod = $result->getPaymentMethod();
        if ($paymentMethod instanceof PaymentMethodBankCard) {
            $card = $paymentMethod->getCard();
            $firstPart6 = $card?->first6 ?: '******';
            $lastPart4 = $card?->last4 ?: '****';
        } else {
            $firstPart6 = '******';
            $lastPart4 = '****';
        }

        switch ($result->status) {
            case PaymentStatus::WAITING_FOR_CAPTURE:
                $paymentStatus = PaymentStatusEnum::APPROVED;
                break;
            case PaymentStatus::CANCELED:
                $refundedAmount = $result->refundedAmount->getIntegerValue();
                if ($refundedAmount > 0) {
                    $paymentStatus = $result->paid ? PaymentStatusEnum::REFUNDED : PaymentStatusEnum::REVERSED;
                } else {
                    $paymentStatus = PaymentStatusEnum::DECLINED;
                }
                break;
            case PaymentStatus::SUCCEEDED:
                $paymentStatus = PaymentStatusEnum::DEPOSITED;
                break;
            case PaymentStatus::PENDING:
            default:
                $paymentStatus = PaymentStatusEnum::CREATED;
                break;
        }

        return new PaymentStatusDto(
            $result->status,
            $paymentStatus,
            $paymentMethod?->id,
            "$firstPart6******$lastPart4",
        );
    }

    protected function makeCreatedPaymentResult(CreatePaymentResponse $result): CreatedPaymentDto
    {
        $statusDto = $this->makeStatusDto($result);
        return new CreatedPaymentDto(
            $result->id,
            $result->confirmation?->getConfirmationUrl(),
            $statusDto
        );
    }
}
