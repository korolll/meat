<?php

namespace App\Services\Models\Order;

use App\Models\Order;
use Arhitector\Yandex\Client\Exception\NotFoundException;
use Arhitector\Yandex\Disk;
use GuzzleHttp\Psr7\Utils;

class CashFileController implements CashFileControllerInterface
{
    const FOLDER_NAME = 'S-mar zakazy';

    /**
     * @var \Arhitector\Yandex\Disk
     */
    private Disk $disk;

    /**
     *
     */
    public function __construct(Disk $disk)
    {
        $this->disk = $disk;
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return void
     */
    public function generateFile(Order $order): void
    {
        $fileContent = $this->generateFileContent($order);
        $fileContent = mb_convert_encoding($fileContent, "windows-1251", "utf-8");
        $fopen = fopen('php://temp', 'r+');

        try {
            fwrite($fopen, $fileContent);
            fseek($fopen, 0);
            $path = $this->makePath($order) . '/' . $this->makeFileName($order);
            $resource = $this->disk->getResource($path, 1);
            $resource->upload($fopen);
        } finally {
            @fclose($fopen);
        }
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    public function deleteFile(Order $order): bool
    {
        $fullPath = $this->makePath($order, false) . '/' . $this->makeFileName($order);
        $resource = $this->disk->getResource($fullPath, 1);
        try {
            return $resource->delete(true) !== false;
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return string
     */
    protected function generateFileContent(Order $order): string
    {
        $productsData = $order->orderProducts()
            ->join('products', 'products.uuid', 'order_products.product_uuid')
            ->join('assortments', 'assortments.uuid', 'products.assortment_uuid')
            ->toBase()
            ->get([
                'assortments.article',
                'order_products.total_amount_with_discount',
                'order_products.quantity',
                'order_products.price_with_discount',
            ]);

        $now = now();
        $rows = [];
        $rows[] = 'Интернет заказ №' . $order->number . ';'
            . '1;'
            . $now->format('d.m.Y') . ';'
            . $now->format('H:i:s') . ';'
            . $order['total_price'] . ';;'
            . $order['number'] . ';;;';

        foreach ($productsData as $item) {
            $rows[] = '1;'
                . $item->article . ';;'
                . $item->price_with_discount . ';'
                . $item->quantity . ';';
        }

        return join("\n", $rows);
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return string
     */
    protected function makeFileName(Order $order): string
    {
        return 'order' . $order->number . '.opn';
    }

    /**
     * @param \App\Models\Order $order
     * @param bool              $generateFolders
     *
     * @return string
     */
    protected function makePath(Order $order, bool $generateFolders = true): string
    {
        $path = [
            static::FOLDER_NAME,
            $order->store_user_uuid,
        ];

        $resultPath = '';
        foreach ($path as $level) {
            $resultPath .= '/' . $level;
            if (! $generateFolders) {
                continue;
            }

            $resource = $this->disk->getResource($resultPath, 1);
            try {
                $resource->create();
            } catch (Disk\Exception\AlreadyExistsException $exception) {

            }
        }

        return $resultPath;
    }
}
