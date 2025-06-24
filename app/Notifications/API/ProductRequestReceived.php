<?php

namespace App\Notifications\API;

use App\Models\ProductRequest;
use App\Services\Documents\Spreadsheets\SupplierProductRequestSpreadsheet;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ProductRequestReceived extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var ProductRequest
     */
    public $productRequest;

    /**
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = $this->makeMail();

        try {
            $mail->attachData($this->makeMailAttachment(), "{$mail->subject}.xlsx");
        } catch (\Throwable $e) {
            report($e);
        }

        return $mail;
    }

    /**
     * @return MailMessage
     */
    protected function makeMail()
    {
        $customer = $this->productRequest->customerUser;
        $customerOrganizationName = $customer->organization_name;

        $expectedDeliveryDate = $this->productRequest->expected_delivery_date->format('d.m.Y');
        $createdAt = $this->productRequest->created_at->format('d.m.Y');

        $mail = (new MailMessage)
            ->subject("Заявка от {$createdAt} по МСК")
            ->line("Вам поступила новая заявка на отгрузку от {$customerOrganizationName} на {$expectedDeliveryDate} по МСК.")
            ->line('Если у Вас есть вопросы по полученному заказу, либо нет возможности выполнить заказ полностью или частично, прошу связаться с нами по указанным ниже контактам:')
            ->line('Тел.: ' . $customer->phone)
            ->line('Email: ' . $customer->email)
            ->line('ФИО: ' . $customer->full_name)
            ->line('Для того, чтобы просмотреть детали заявки перейдите в свой профиль на платформе Тилси.')
            ->action('Перейти в профиль', url_frontend('/shipment'));

        return $mail;
    }

    /**
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function makeMailAttachment()
    {
        return SupplierProductRequestSpreadsheet::make($this->productRequest)->asBinary();
    }
}
