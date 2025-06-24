<?php

namespace App\Services\Money\Acquire;

use App\Models\PaymentVendor;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\HttpClient\GuzzleAdapter;


class SberbankAcquire implements AcquireInterface
{
    /**
     * @var \Voronkovich\SberbankAcquiring\Client
     */
    private Client $client;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $config['httpClient'] = new GuzzleAdapter(new \GuzzleHttp\Client());
        $this->client = new Client($config);
    }

    public static function getValidConfig(array $config): ?array
    {
        $valid = isset($config['userName'])
            && isset($config['password'])
            && $config['userName']
            && $config['password'];

        if (!$valid) {
            return null;
        }

        return Arr::only($config, ['userName', 'password']);
    }

    public function getVendorId(): string
    {
        return PaymentVendor::ID_SBERBANK;
    }

    public function paymentOrderBinding(string $orderId, string $bindingId, array $data = []): ?string
    {
        $result = $this->client->paymentOrderBinding($orderId, $bindingId, $data);
        if (isset($result['errorTypeName']) && $result['errorTypeName']) {
            return (string)$result['errorTypeName'];
        }

        return null;
    }

    public function getPaymentStatus(string $paymentId): PaymentStatusDto
    {
        $result = $this->client->getOrderStatus($paymentId);
        return $this->makeStatusDto($result);
    }

    public function isConfirmationNeeded(PaymentStatusDto $dto): bool
    {
        return $dto->status === PaymentStatusEnum::CREATED;
    }

    public function registerPaymentForBinding(string $clientId, string $orderNumber, int $amount, string $returnUrl, string $failUrl): CreatedPaymentDto
    {
        $orderNumber = $this->toHexIfUuid($orderNumber);
        $data = array_merge(['pageView' => 'MOBILE'], compact('failUrl', 'clientId'));
        $result = $this->client->registerOrder($orderNumber, $amount, $returnUrl, $data);

        return $this->makeCreatedPaymentResult($result);
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
        $orderNumber = $this->toHexIfUuid($orderNumber);
        $data = array_merge(['features' => 'AUTO_PAYMENT'], compact('failUrl', 'clientId'));
        if ($isHold) {
            $result = $this->client->registerOrderPreAuth($orderNumber, $amount, $returnUrl, $data);
        } else {
            $result = $this->client->registerOrder($orderNumber, $amount, $returnUrl, $data);
        }

        return $this->makeCreatedPaymentResult($result);
    }

    protected function makeStatusDto(array $result)
    {
        return new PaymentStatusDto(
            // Original status and status must be equal
            $result['orderStatus'],
            PaymentStatusEnum::from($result['orderStatus']),
            Arr::get($result, 'bindingInfo.bindingId'),
            Arr::get($result, 'cardAuthInfo.maskedPan'),
        );
    }

    protected function makeCreatedPaymentResult(array $result): CreatedPaymentDto
    {
        $statusDto = $this->getPaymentStatus($result['orderId']);
        return new CreatedPaymentDto(
            $result['orderId'],
            $result['formUrl'],
            $statusDto
        );
    }

    public function closeOfdReceipt(string $orderNumber, int $amount, array $data = []): void
    {
        $orderNumber = $this->toHexIfUuid($orderNumber);
        $data['orderNumber'] = $orderNumber;

        if ($amount > 0) {
            $data['amount'] = $amount;
        }

        if (isset($data['orderBundle']) && is_array($data['orderBundle'])) {
            $data['orderBundle'] = \json_encode($data['orderBundle']);
        }

        $this->client->execute('closeOfdReceipt.do', $data);
    }

    public function deposit(string $paymentId, int $amount): void
    {
        $this->client->deposit($paymentId, $amount);
    }

    public function refund(string $paymentId, int $amount): void
    {
        $this->client->refundOrder($paymentId, $amount);
    }

    public function reverse(string $paymentId): void
    {
        $this->client->reverseOrder($paymentId);
    }

    public function cancelBinding(string $bindingId): void
    {
        $this->client->unBindCard($bindingId);
    }

    /**
     * @param string $orderNumber
     *
     * @return string
     */
    protected function toHexIfUuid(string $orderNumber): string
    {
        if (! Uuid::isValid($orderNumber)) {
            return $orderNumber;
        }

        return Uuid::getFactory()
            ->fromString($orderNumber)
            ->getHex()
            ->toString();
    }
}
