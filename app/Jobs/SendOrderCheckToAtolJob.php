<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Integrations\Atol\AtolOnlineClientInterface;
use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderCheckToAtolJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    protected bool $isAdvance;
    protected bool $isCancel;

    /**
     * @param \App\Models\Order $order
     * @param bool              $isAdvance
     * @param bool              $isCancel
     */
    public function __construct(Order $order, bool $isAdvance, bool $isCancel = false)
    {
        $this->order = $order;
        $this->isAdvance = $isAdvance;
        $this->isCancel = $isCancel;
    }

    /**
     * @return void
     */
    public function handle()
    {
        /** @var AtolSellRequestGeneratorInterface $generator */
        $generator = app(AtolSellRequestGeneratorInterface::class);
        /** @var AtolOnlineClientInterface $client */
        $client = app(AtolOnlineClientInterface::class);

        try {
            $data = $generator->generate($this->order, $this->isAdvance);
        } catch (Throwable $exception) {
            Log::channel('atol')->error('Не удалось создать данные для отправки чека по заказу', [
                'order_uuid' => $this->order->uuid,
                'exception' => $exception
            ]);

            return;
        }

        Log::channel('atol')->debug('Создали данные для чека', [
            'order_uuid' => $this->order->uuid,
            'data' => $data
        ]);

        try {
            if ($this->isCancel) {
                $responseData = $client->sellRefund($data);
            } else {
                $responseData = $client->sell($data);
            }
        } catch (ClientException $exception) {
            Log::channel('atol')->error($exception->getMessage(), [
                'content' => $exception->getResponse()->getBody()
            ]);
            throw $exception;
        }

        Log::channel('atol')->debug($this->isCancel ? 'Чек отмены успешно отправлен' : 'Чек продажи успешно отправлен', [
            'order_uuid' => $this->order->uuid,
            'response_data' => $responseData
        ]);
    }
}
