<?php

namespace Tests\Feature\API\Profile\ProductRequests;

use App\Exceptions\ClientException;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use App\Services\Traits\CollectErrors;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCaseNotificationsFake;

class ProductRequestImportTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     * @test
     * @throws Exception
     */
    public function import()
    {
        $expectedDeliveryDate = Carbon::now()->addDays(10);
        // Import creator
        /**
         * @var $userAdmin User
         */
        $userAdmin = factory(User::class)->state('admin')->create();

        // Products supplier
        /**
         * @var $userSupplier User
         */
        $userSupplier = factory(User::class)->state('supplier')->create();

        // Users for create product requests
        /**
         * @var $userStore1 User
         */
        $userStore1 = factory(User::class)->state('store')->create();
        /**
         * @var $userStore2 User
         */
        $userStore2 = factory(User::class)->state('store')->create();
        /**
         * @var $userStore3 User
         */
        $userStore3 = factory(User::class)->state('store')->create();

        /**
         * @var $product1 Product
         */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
        ]);
        /**
         * @var $product2 Product
         */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
        ]);
        /**
         * @var $product3 Product
         */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
        ]);

        /**
         * @var $priceList PriceList
         */
        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'price_list_status_id' => 'current'
        ]);
        $priceList->products()->attach([
            $product1->uuid => ['price_new' => 100],
            $product2->uuid => ['price_new' => 200],
            $product3->uuid => ['price_new' => 300],
        ]);

        $importData = [
            [
                $expectedDeliveryDate->format('d.m.Y'),
                null,
                null,
                null,
                null
            ],
            [
                'шк',
                'Наименование товара',
                $userStore1->email,
                $userStore2->email,
                $userStore3->email,
            ],
            [
                $product1->assortment->barcodes->pluck('barcode')->implode(', '),
                $product1->assortment->name,
                $product1->min_quantity_in_order * $product1->quantum * random_int(1, 10),
                $product1->min_quantity_in_order * $product1->quantum * random_int(1, 10),
                $product1->min_quantity_in_order * $product1->quantum * random_int(1, 10),
            ],
            [
                $product2->assortment->barcodes->pluck('barcode')->implode(', '),
                $product2->assortment->name,
                $product2->min_quantity_in_order * $product2->quantum * random_int(1, 10),
                $product2->min_quantity_in_order * $product2->quantum * random_int(1, 10),
                null
            ],
            [
                $product3->assortment->barcodes->pluck('barcode')->implode(', '),
                $product3->assortment->name,
                $product3->min_quantity_in_order * $product3->quantum * random_int(1, 10),
                null,
                null,
            ],
            [
                'Итого',
                null,
                null,
                null,
                null
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($importData);

        $writer = new Xlsx($spreadsheet);

        $storageTest = Storage::disk('testing');
        $testImportFileDir = $userAdmin->uuid . time();
        $storageTest->makeDirectory($testImportFileDir);
        $testImportFileName = 'import.xlsx';
        $testImportFilePath = $storageTest->path($testImportFileDir) . DIRECTORY_SEPARATOR . $testImportFileName;
        $writer->save($testImportFilePath);

        $this->assertFileExists($testImportFilePath);

        $uploadedFileResource = fopen($testImportFilePath, 'rb');
        $uploadedFile = new File($testImportFilePath, $uploadedFileResource);

        $response = $this->be($userAdmin)->postJson('/api/profile/product-requests/import/' . $userSupplier->uuid, [
            'file' => $uploadedFile
        ]);

        unset($uploadedFile);
        fclose($uploadedFileResource);

        $newProductRequests = @json_decode($response->getContent(), true);

        $response->assertStatus(Response::HTTP_CREATED);

        $importData = array_slice($importData, 2, 3);
        $importDataNew = [];
        foreach ($importData as $key => $importDataItem) {
            $productUuid = ${'product' . ($key + 1)}->uuid;
            $importDataNew[$productUuid] = array_slice($importDataItem, 2);
        }

        foreach ($newProductRequests['new_product_request_uuids'] as $newProductRequestUuid => $productsArray) {
            foreach ($productsArray as $key => $productUuid) {
                $this->assertDatabaseHas('product_product_request', [
                    'product_request_uuid' => $newProductRequestUuid,
                    'product_uuid' => $productUuid,
                    'quantity' => array_shift($importDataNew[$productUuid])
                ]);
            }
        }

        $storageTest->deleteDirectory($testImportFileDir);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     * @test
     * @throws Exception
     */
    public function checkExceptions()
    {
        $expectedDeliveryDate = Carbon::now()->addDays(10);
        // Import creator
        /**
         * @var $userAdmin User
         */
        $userAdmin = factory(User::class)->state('admin')->create();

        // Products supplier
        /**
         * @var $userSupplier User
         */
        $userSupplier = factory(User::class)->state('supplier')->create();

        // Users for create product requests
        /**
         * @var $userStore1 User
         */
        $userStore1 = factory(User::class)->state('store')->create();
        /**
         * @var $userStore2 User
         */
        $userStore2 = factory(User::class)->state('store')->create();
        /**
         * @var $userStore3 User
         */
        $userStore3 = factory(User::class)->state('store')->create();

        /**
         * @var $product1 Product
         */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
            'quantum' => $this->faker->numberBetween(2, 10)
        ]);
        /**
         * @var $product2 Product
         */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
            'quantum' => $this->faker->numberBetween(2, 10)
        ]);
        /**
         * @var $product3 Product
         */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'delivery_weekdays' => [$expectedDeliveryDate->dayOfWeek],
            'min_delivery_time' => 1,
            'quantum' => $this->faker->numberBetween(2, 10)
        ]);

        /**
         * @var $priceList PriceList
         */
        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $userSupplier->uuid,
            'price_list_status_id' => 'current'
        ]);
        $priceList->products()->attach([
            $product1->uuid => ['price_new' => 100],
            $product2->uuid => ['price_new' => 200],
            $product3->uuid => ['price_new' => 300],
        ]);

        $importData = [
            [
                $expectedDeliveryDate->format('d.m.Y'),
                null,
                null,
                null,
                null
            ],
            [
                'шк',
                'Наименование товара',
                $userStore1->email,
                $userStore2->email,
                $userStore3->email,
            ],
            [
                $product1->assortment->barcodes->pluck('barcode')->implode(', '),
                $product1->assortment->name,
                $product1->quantum * 1.4,
                $product1->min_quantity_in_order * $product1->quantum * 10,
                $product1->quantum * 1.4,
            ],
            [
                $product2->assortment->barcodes->pluck('barcode')->implode(', '),
                $product2->assortment->name,
                $product2->min_quantity_in_order * $product2->quantum * 10,
                $product2->quantum * 1.4,
                null
            ],
            [
                $product3->assortment->barcodes->pluck('barcode')->implode(', '),
                $product3->assortment->name,
                $product3->min_quantity_in_order * $product3->quantum * 10,
                null,
                null,
            ],
            [
                'Итого',
                null,
                null,
                null,
                null
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($importData);

        $writer = new Xlsx($spreadsheet);

        $storageTest = Storage::disk('testing');
        $testImportFileDir = $userAdmin->uuid . time();
        $storageTest->makeDirectory($testImportFileDir);
        $testImportFileName = 'import.xlsx';
        $testImportFilePath = $storageTest->path($testImportFileDir) . DIRECTORY_SEPARATOR . $testImportFileName;
        $writer->save($testImportFilePath);

        $this->assertFileExists($testImportFilePath);

        $uploadedFileResource = fopen($testImportFilePath, 'rb');
        $uploadedFile = new File($testImportFilePath, $uploadedFileResource);

        $response = $this->be($userAdmin)->postJson('/api/profile/product-requests/import/' . $userSupplier->uuid, [
            'file' => $uploadedFile
        ]);

        unset($uploadedFile);
        fclose($uploadedFileResource);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        /**
         * @var ClientException $exception
         */
        $exception = $response->baseResponse->exception;
        $this->assertEquals($exception->getExceptionCode(), CollectErrors::$EXCEPTION_CODE);
        $errorMessage = $exception->getMessage();
        $errorMessage = json_decode($errorMessage, true);
        // TODO: Fix this fckg shit
        $this->assertNotEmpty($errorMessage);
    }
}
