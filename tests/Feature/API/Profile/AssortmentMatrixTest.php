<?php

namespace Tests\Feature\API\Profile;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\AssortmentVerifyStatus;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserType;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCaseNotificationsFake;

class AssortmentMatrixTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param string $filterBy
     *
     * @test
     * @testWith ["tags"]
     *           ["barcodes"]
     *           ["catalog_uuid"]
     *           ["assortment_properties"]
     */
    public function index(string $filterBy)
    {
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);

        /** @var User $self */
        $self = factory(User::class)->create([
            'user_type_id' => UserType::ID_DISTRIBUTION_CENTER,
        ]);

        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $self->uuid,
            'quantity' => 5,
        ]);

        // region Продукты других пользователей
        $user2 = factory(User::class)->state('supplier')->create();
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $user2->uuid,
            'price' => 5.3,
        ]);

        /** @var PriceList $publicPriceList */
        $publicPriceList = factory(PriceList::class)->create([
            'user_uuid' => $user2->uuid,
            'customer_user_uuid' => null,
            'price_list_status_id' => PriceListStatus::CURRENT
        ]);
        $publicPriceList->products()->attach([$product2->uuid => ['price_new' => 100]]);

        /** @var PriceList $privatePriceList */
        $privatePriceList = factory(PriceList::class)->create([
            'user_uuid' => $user2->uuid,
            'customer_user_uuid' => $self->uuid,
            'price_list_status_id' => PriceListStatus::CURRENT
        ]);
        $privatePriceList->products()->attach([$product2->uuid => ['price_new' => 1000]]);

        factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => factory(User::class)->state('supplier')->create()->uuid,
            'price' => 1500,
        ]);
        // endregion

        $type = SupplierProductRequest::MORPH_TYPE_ALIAS;
        factory(WarehouseTransaction::class)->create([
            'quantity_old' => 15,
            'quantity_new' => 8,
            'product_uuid' => $product->uuid,
            'reference_type' => $type,
            'reference_id' => $product->uuid,
        ]);
        factory(WarehouseTransaction::class)->create([
            'quantity_old' => 8,
            'quantity_new' => 5,
            'product_uuid' => $product->uuid,
            'reference_type' => $type,
            'reference_id' => $product->uuid,
        ]);

        $tagOne = factory(Tag::class)->create();
        $tagTwo = factory(Tag::class)->create();
        $assortment->tags()->attach($tagOne);
        $assortment->tags()->attach($tagTwo);

        $image = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);
        $assortment->images()->attach($image, ['file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE]);

        if ($filterBy === 'assortment_properties') {

            $value1 = 'qweqweqweqweqw';
            $value2 = 'asdasdasdasdas';

            /**
             * @var $assortmentProperty1 AssortmentProperty
             * @var $assortmentProperty2 AssortmentProperty
             */
            $assortmentProperty1 = factory(AssortmentProperty::class)->make();
            $assortmentProperty2 = factory(AssortmentProperty::class)->make();
            $assortment->assortmentProperties()->save($assortmentProperty1, ['value' => $value1]);
            $assortment->assortmentProperties()->save($assortmentProperty2, ['value' => $value2]);

            $data = [
                'assortment_property' => [
                    'uuid' => $assortmentProperty1->uuid,
                    'value' => $value1,
                    'operator' => '=',
                ]
            ];
        } else {
            if ($filterBy === 'tags') {
                $filter = $tagOne->name;
            } else {
                $filter = $assortment->{$filterBy};
            }

            if ($filterBy === 'barcodes') {
                /**
                 * @var $filter Collection
                 */
                $filter = $filter->pluck('barcode')->toArray();
            }
            $data = [
                'where' => [
                    [$filterBy, 'in', $filter]
                ]
            ];
        }

        $self->assortmentMatrix()->attach($assortment);

        $json = $this->be($self)->json('get', '/api/profile/assortment-matrix', $data);

        if ($json->baseResponse->status() !== 200) {
            $json->dump();
        }
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                    'quantity' => 5,
                    'week_sales' => 10,
                    'price_min' => 1000,
                    'short_name' => $assortment->short_name,
                    'catalog_uuid' => $assortment->catalog_uuid,
                    'barcodes' => $assortment->barcodes->pluck('barcode')->toArray(),
                    'tags' => [$tagOne->name, $tagTwo->name],
                    'images' => [['uuid' => $image->uuid]]
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexFromSupplierRequests()
    {
        $user = factory(User::class)->state('distribution-center')->create();

        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
        ]);

        $request1 = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $user->uuid,
        ]);

        $request2 = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $user->uuid,
        ]);

        $request1->products()->attach($product, ['quantity' => 5, 'price' => $product->price]);
        $request2->products()->attach($product, ['quantity' => 5, 'price' => $product->price]);

        $query = http_build_query([
            'supplier_product_requests' => [
                [
                    'uuid' => $request1->uuid,
                ],
                [
                    'uuid' => $request2->uuid,
                ],
            ],
        ]);

        $json = $this->be($user)->getJson('/api/profile/assortment-matrix/from-supplier-product-requests?' . $query);
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->assortment_uuid,
                    'order_quantity' => 10,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $assortment = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);
        $self = factory(User::class)->create([
            'user_type_id' => UserType::ID_DISTRIBUTION_CENTER,
        ]);

        $json = $this->be($self)->postJson('/api/profile/assortment-matrix', [
            'assortment_uuid' => $assortment->uuid,
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('assortment_matrices', [
            'user_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
        ]);
        $this->assertDatabaseHas('products', [
            'user_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $assortment = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);
        /** @var User $self */
        $self = factory(User::class)->create([
            'user_type_id' => UserType::ID_DISTRIBUTION_CENTER,
        ]);

        $self->assortmentMatrix()->attach($assortment);

        $json = $this->be($self)->deleteJson("/api/profile/assortment-matrix/{$assortment->uuid}");
        $json->assertSuccessful();
        $this->assertDatabaseMissing('assortment_matrices', [
            'user_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
        ]);
    }
}
