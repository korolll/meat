<?php

namespace Database\Factories;

use App\Models\PaymentVendor;
use App\Models\PaymentVendorSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentVendorSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentVendorSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_vendor_id' => $this->faker->randomElement([
                PaymentVendor::ID_SBERBANK,
                PaymentVendor::ID_YOOKASSA,
            ]),
            'config' => function (array $setting) {
                return match ($setting['payment_vendor_id']) {
                    PaymentVendor::ID_SBERBANK => [
                        'userName' => $this->faker->userName,
                        'password' => $this->faker->password,
                    ],
                    PaymentVendor::ID_YOOKASSA => [
                        'authToken' => $this->faker->uuid
                    ],
                };
            },
        ];
    }
}
