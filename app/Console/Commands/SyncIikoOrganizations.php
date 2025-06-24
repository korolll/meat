<?php

namespace App\Console\Commands;

use App\Events\FileUploaded;
use App\Models\Assortment;
use App\Models\AssortmentUnit;
use App\Models\AssortmentVerifyStatus;
use App\Models\Catalog;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\LegalForm;
use App\Models\Product;
use App\Models\ProductionStandard;
use App\Models\User;
use App\Models\UserType;
use App\Models\UserVerifyStatus;
use App\Services\Integrations\Iiko\IikoClientInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile as LaravelUploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class SyncIikoOrganizations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iiko:sync';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация магазинов из iiko';

    /**
     * @var IikoClientInterface
     */
    private IikoClientInterface $client;

    /**
     * @var Client
     */
    private Client $guzzle;

    /**
     * @var string|null
     */
    private ?string $adminId = null;

    /**
     * @var array
     */
    private array $cache = [
        'groups' => [],
        'assortments' => []
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->client = app(IikoClientInterface::class);
        $this->guzzle = new Client();
        $firstAdmin = User::admin()->first();
        if (! $firstAdmin) {
            Log::channel('iiko')->error('Не удалось найти администратора');
            return;
        }

        $this->adminId = $firstAdmin->uuid;
        $organizations = $this->client->getOrganizations();
        $organizationIds = [];
        foreach ($organizations as $organization) {
            $organizationIds[] = $organization['id'];
        }

        $stopLists = $this->client->getStopListsMap($organizationIds);

        $products = Product::all();

        foreach ($products as $item) {
            $item->quantity = 0;
            $item->save();
        }

        foreach ($organizations as $organization) {
            $menu = $this->client->getMenu($organization['id']);
            $stops = Arr::get($stopLists, $organization['id'], []);
            $this->syncOrganization($organization, $menu, $stops);
            sleep(60);
        }
    }

    /**
     * @param array $organization
     * @param array $menu
     * @param array $stops
     *
     * @return void
     */
    protected function syncOrganization(array $organization, array $menu, array $stops): void
    {
        $user = $this->createUser($organization);
        $this->syncGroups($menu['groups']);
        $this->syncMenuProducts($user, $menu['products'], $stops);
    }

    /**
     * @param array $organization
     *
     * @return void
     */
    protected function createUser(array $organization): User
    {
        $user = User::findOrNew($organization['id']);
        if ($user->exists) {
            return $user;
        }

        $user->uuid = $organization['id'];
        $user->forceFill([
            'user_type_id' => UserType::ID_STORE,
            'full_name' => $organization['name'],
            'legal_form_id' => LegalForm::ID_IP,
            'organization_name' => $organization['name'],
            'organization_address' => $organization['restaurantAddress'],
            'address' => $organization['restaurantAddress'],
            'inn' => $organization['inn'],
            'user_verify_status_id' => UserVerifyStatus::ID_APPROVED,
            'is_email_verified' => true,
            'address_latitude' => $organization['latitude'],
            'address_longitude' => $organization['longitude'],

            // def
            'email' => $organization['id'] . '@example.com',
            'phone' => '+70000000000',
            'ogrn' => '0000000000000'
        ]);

        $user->save();
        return $user;
    }

    /**
     * @param array $groups
     *
     * @return void
     */
    protected function syncGroups(array $groups): void
    {
        $copy = $groups;
        while ($copy) {
            foreach ($copy as $key => $group) {
                $id = $group['id'];
                if (isset($this->cache['groups'][$id])) {
                    // Already handled
                    unset($groups[$key]);
                    continue;
                }

                $parentGroupId = $group['parentGroup'];
                if ($parentGroupId && ! isset($this->cache['groups'][$parentGroupId])) {
                    // We did not handle parent group yet
                    continue;
                }

                $this->createOrUpdateCatalog($group);
                unset($groups[$key]);
                $this->cache['groups'][$id] = 1;
            }

            $copy = $groups;
        }
    }

    /**
     * @param array $group
     *
     * @return void
     */
    protected function createOrUpdateCatalog(array $group): void
    {
        $firstImageUrl = Arr::get($group, 'imageLinks.0');
        $imageUuid = null;
        if ($firstImageUrl) {
            $imageUuid = $this->syncImage($firstImageUrl, FileCategory::ID_CATALOG_IMAGE);
        }

        $catalog = Catalog::withTrashed()->findOrNew($group['id']);
        $catalog->uuid = $group['id'];
        $catalog->name = $group['name'];
        $catalog->catalog_uuid = $group['parentGroup'];
        $catalog->user_uuid = null;
        $catalog->image_uuid = $imageUuid;
        $catalog->save();
    }

    /**
     * @param User $user
     * @param array            $products
     * @param array            $stops
     *
     * @return void
     */
    protected function syncMenuProducts(User $user, array $products, array $stops): void
    {
        $catalog = $user->catalogs()->first();
        if (! $catalog) {
            $catalog = new Catalog();
            $catalog->user_uuid = $user->uuid;
            $catalog->name = 'Все';
            $catalog->save();
        }

        foreach ($products as $product) {
            $id = $product['id'];
            $type = Arr::get($product, 'type', '');
            if (strtolower($type) === 'modifier') {
                continue;
            }

            if (! isset($this->cache['assortments'][$id])) {
                $this->createOrUpdateAssortment($product);
                $this->cache['assortments'][$id] = 1;
            }

            $this->createOrUpdateProduct($user, $catalog->uuid, $product, $stops);
        }
    }

    /**
     * @param array $product
     *
     * @return void
     */
    protected function createOrUpdateAssortment(array $product): void
    {
        $firstImageUrl = Arr::get($product, 'imageLinks.0');
        $imageUuid = null;
        if ($firstImageUrl) {
            $imageUuid = $this->syncImage($firstImageUrl, FileCategory::ID_ASSORTMENT_IMAGE);
        }

        $unit = $product['measureUnit'] == 'кг' ? AssortmentUnit::ID_KILOGRAM : AssortmentUnit::ID_PIECE;
        $model = Assortment::findOrNew($product['id']);
        $model->uuid = $product['id'];

        if ($product['seoText']== null){
                $seoText = 0;
        }

        $model->forceFill([
            'catalog_uuid' => $product['parentGroup'],
        ]);

        if (! $model->exists) {
            $model->forceFill([
                'catalog_uuid' => $product['parentGroup'],
                'name' => $product['name'],
                'assortment_unit_id' => $unit,
                'ingredients' => $product['seoDescription'],
                'shelf_life' => $seoText,
                'description' => $product['description'],
                'country_id' => 'RU',
                'temperature_max' => 40,
                'production_standard_id' => ProductionStandard::ID_GOST,
                'production_standard_number' => '11111',
                'is_storable' => true,
                'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
                'weight' => $unit == AssortmentUnit::ID_KILOGRAM ? $product['weight'] * 1000 : $product['weight'],
                'nds_percent' => 20,
                'article' => $product['code']
            ]);
        }

        $model->save();
        if ($imageUuid && $model->images()->get()->isEmpty()) {
            $model->images()->sync([$imageUuid => [
                'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE
            ]]);
        }
    }

    /**
     * @param User $user
     * @param string           $catalogId
     * @param array            $product
     * @param array            $stops
     *
     * @return void
     */
    protected function createOrUpdateProduct(User $user, string $catalogId, array $product, array $stops): void
    {
        $model = $user->products()
            ->where('assortment_uuid', $product['id'])
            ->firstOrNew();

        $price = Arr::get($product, 'sizePrices.0.price.currentPrice');
        if (! $price) {
            Log::channel('iiko')->error('Не удалось создать продукт: цена не обнаружена', [
                'product' => $product
            ]);
            return;
        }

        $quantity = 9999;
        $tags = Arr::get($product, 'tags');
        if (!in_array('топ', $tags)) {
                $quantity = 0;
            }

        $model->forceFill([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $product['id'],
            'catalog_uuid' => $catalogId,
            'price' => $price,
            'price_recommended' => $price,
            'quantity' => $quantity,
        ]);

        $justCreated = false;
        if (! $model->exists) {
            $justCreated = true;
            $quantity = 9999;
            if (isset($stops[$product['id']])) {
                $quantity = 0;
            }


        $tags = Arr::get($product, 'tags');
        if (!in_array('топ', $tags)) {
                $quantity = 0;
            }

            $model->forceFill([
                'quantum' => 1,
                'quantity' => $quantity,
                'min_quantum_in_order' => 1,
            ]);
        }

        $model->save();
        if ($justCreated) {
            $user->assortmentMatrix()->attach($product['id']);
        }
    }

    /**
     * @param string $url
     * @param string $categoryId
     *
     * @return string|null
     */
    protected function syncImage(string $url, string $categoryId): ?string
    {
        $parsed = parse_url($url);
        $urlPath = Arr::get($parsed, 'path');
        if (! $urlPath) {
            return null;
        }

        $hash = hash('sha256', $urlPath);
        $image = File::where('user_uuid', $this->adminId)
            ->where('file_category_id', $categoryId)
            ->where('original_name', $hash)
            ->first();

        if ($image) {
            return $image->uuid;
        }

        $tmp = tempnam("/tmp", "iiko_");
        if (! $tmp) {
            Log::channel('iiko')->error('Не удалось создать временный файл для скачивания изображения');
            return null;
        }

        try {
            $response = $this->guzzle->get($url, [
                RequestOptions::SINK => $tmp
            ]);
            $mimeType = $response->getHeaderLine('Content-Type');
            $file = new UploadedFile($tmp, $tmp, $mimeType);
            $uploadedFile = LaravelUploadedFile::createFromBase($file);
            $path = $uploadedFile->storePublicly($categoryId);
            if (! $path) {
                throw new Exception('Не удалось сохранить файл на диск');
            }

            $newFile = new File([
                'file_category_id' => $categoryId,
                'original_name' => $hash,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            $newFile->user_uuid = $this->adminId;
            $newFile->save();

            FileUploaded::dispatch($newFile);
            return $newFile->uuid;
        } catch (Throwable $exception) {
            Log::channel('iiko')->error('Не удалось сохранить файл', [
                'exception' => $exception,
                'url' => $url,
            ]);
            return null;
        } finally {
            @unlink($tmp);
        }
    }
}
