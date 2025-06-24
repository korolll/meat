<?php

namespace App\Services\Money\Acquire;

use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;

interface AcquireInterface
{
    public function getVendorId(): string;

    public function getPaymentStatus(string $paymentId): PaymentStatusDto;

    public static function getValidConfig(array $config): ?array;

    public function isConfirmationNeeded(PaymentStatusDto $dto): bool;

    public function paymentOrderBinding(string $orderId, string $bindingId, array $data = []): ?string;

    public function registerPaymentForBinding(string $clientId, string $orderNumber, int $amount, string $returnUrl, string $failUrl): CreatedPaymentDto;

    public function registerAutoPayment(
        string $bindingId,
        string $clientId,
        string $orderNumber,
        int $amount,
        string $returnUrl,
        string $failUrl,
        bool $isHold = false
    ): CreatedPaymentDto;

    public function closeOfdReceipt(string $orderNumber, int $amount, array $data = []): void;

    public function deposit(string $paymentId, int $amount): void;

    public function refund(string $paymentId, int $amount): void;

    public function reverse(string $paymentId): void;

    public function cancelBinding(string $bindingId): void;
}
