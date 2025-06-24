<?php

namespace Tests\Feature\API;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCaseNotificationsFake;

class ProfileTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function show()
    {
        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson('/api/profile');

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $self->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $self = factory(User::class)->create();
        $user = factory(User::class)->make();
        $file = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_USER_FILE,
        ]);

        $json = $this->be($self)->putJson('/api/profile', array_merge($user->only([
            'user_type_id',
            'full_name',
            'legal_form_id',
            'organization_name',
            'organization_address',
            'address',
            'phone',
            'password',
            'inn',
            'kpp',
            'ogrn',
            'region_uuid',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => 'hello kitty'],
            ],
        ]));

        $data = [
            'uuid' => $self->uuid,
            'ogrn' => $user->ogrn,
            'region_uuid' => $user->region_uuid,
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => 'hello kitty',
                ],
            ],
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('users', Arr::except($data, 'files'));
    }

    /**
     * @test
     */
    public function updateStore()
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create([
            'has_parking' => false,
            'has_ready_meals' => false,
            'has_atms' => false,
        ]);
        /** @var User $user */
        $user = factory(User::class)
            ->states([
                'store',
                'has-image'
            ])
            ->create([
                'has_parking' => true,
                'has_ready_meals' => true,
                'has_atms' => true,
            ]);

        $user->image->user_uuid = $self->uuid;
        $user->image->save();

        $json = $this->be($self)->putJson('/api/profile', $user->only([
            'user_type_id',
            'full_name',
            'legal_form_id',
            'organization_name',
            'organization_address',
            'address',
            'phone',
            'password',
            'inn',
            'kpp',
            'ogrn',
            'region_uuid',
            'address_latitude',
            'address_longitude',
            'work_hours_from',
            'work_hours_till',
            'brand_name',

            'signer_full_name',
            'ip_registration_certificate_number',
            'date_of_ip_registration_certificate',

            'has_parking',
            'has_ready_meals',
            'has_atms',
            'image_uuid',
        ]));

        $data = [
            'uuid' => $self->uuid,
            'ogrn' => $user->ogrn,
            'brand_name' => $user->brand_name,
            'work_hours_from' => $user->work_hours_from,

            'signer_full_name' => $user->signer_full_name,
            'ip_registration_certificate_number' => $user->ip_registration_certificate_number,
            'date_of_ip_registration_certificate' => $user->date_of_ip_registration_certificate,

            'has_parking' => $user->has_parking,
            'has_ready_meals' => $user->has_ready_meals,
            'has_atms' => $user->has_atms,

            'image' => [
                'uuid' => $user->image->uuid,
                'thumbnails' => [],
                'path' => Storage::url($user->image->path),
            ]
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        unset($data['image']);
        $data['image_uuid'] = $user->image_uuid;
        $this->assertDatabaseHas('users', $data);
    }

    /**
     * @test
     */
    public function updateWithAdditionalEmails()
    {
        $self = factory(User::class)->create();
        $user = factory(User::class)->make();

        $sendData = $user->only([
            'user_type_id',
            'full_name',
            'legal_form_id',
            'organization_name',
            'organization_address',
            'address',
            'phone',
            'password',
            'inn',
            'kpp',
            'ogrn',
        ]);

        $sendData = array_merge($sendData, [
            'additional_emails' => ['123@ss.com', '321@dd.com']
        ]);

        $json = $this->be($self)->putJson('/api/profile', $sendData);

        $data = [
            'uuid' => $self->uuid,
            'ogrn' => $user->ogrn
        ];

        $jsonData = array_merge($data, [
            'additional_emails' => ['123@ss.com', '321@dd.com']
        ]);

        $json->assertSuccessful()->assertJson(['data' => $jsonData]);
        $this->assertDatabaseHas('users', $data);
        $this->assertDatabaseHas('user_additional_emails', [
            'user_uuid' => $self->uuid,
            'email' => '123@ss.com',
        ]);
        $this->assertDatabaseHas('user_additional_emails', [
            'user_uuid' => $self->uuid,
            'email' => '321@dd.com',
        ]);
    }

    /**
     * @param string $legalFormId
     * @param string $signerTypeId
     *
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     *
     * @test
     * @testWith ["ip", "general_director"]
     *          ["ooo", "general_director"]
     *          ["ooo", "confidant"]
     */
    public function supplyContract(string $legalFormId, string $signerTypeId)
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        $user = factory(User::class)->state('distribution-center')->create();

        $self->legal_form_id = $legalFormId;
        $self->signer_type_id = $signerTypeId;

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);

        $privatePriceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => $self->uuid
        ]);

        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'price' => 500
        ]);

        $commonProductPrice = 1000;
        $privateProductPrice = 100;
        $priceList->products()->attach([
            $product->uuid => ['price_new' => $commonProductPrice],
        ]);
        $privatePriceList->products()->attach([
            $product->uuid => ['price_new' => $privateProductPrice],
        ]);

        $fileTmp = tempnam(sys_get_temp_dir(), 'wrd');
        try {
            // Создаем файл
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            $table = $section->addTable();
            $table->addRow()->addCell()->addText('${_t_number}');

            $phpWord->addSection()->addText('${org_name}');
            $phpWord->addSection()->addText('${full_name}');
            $phpWord->save($fileTmp);

            Config::set('app.documents.word.supply_contract.path', $fileTmp);
            Config::set('app.documents.word.supply_contract.user_uuids', $user->uuid);

            $response = $this->be($self)->getJson('/api/profile/supply-contract');
            $response->assertStatus(200);
            /** @var BinaryFileResponse $base */
            $base = $response->baseResponse;

            $this->assertInstanceOf(BinaryFileResponse::class, $base);
            $file = $base->getFile();

            $template = new TemplateProcessorTest($file->getPathname());
            $content = $template->tempDocumentMainPart;

            $this->assertStringContainsString($self->organization_name, $content);
            $this->assertStringContainsString($self->full_name, $content);
            @unlink($file->getPathname());
        } finally {
            @unlink($fileTmp);
        }

        // Проверим счетчик
        $this->assertDatabaseHas('counters', [
            'id' => 'profile_supply_contract_calls_at_' . now()->format('d.m.Y'),
            'value' => 1,
            'step' => 1
        ]);
    }
}

class TemplateProcessorTest extends TemplateProcessor
{
    public $tempDocumentMainPart;
}
