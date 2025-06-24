<?php

use App\Models\LegalForm;
use App\Models\UserType;
use App\Models\UserVerifyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class InsertIntoUsers extends Migration
{
    /**
     * Адрес электронной почты администратора
     */
    const ADMIN_EMAIL_ADDRESS = 'admin@example.com';

    /**
     * @return void
     */
    public function up()
    {
        DB::table('users')->insert([
            'uuid' => Str::orderedUuid(),
            'user_type_id' => UserType::ID_ADMIN,
            'full_name' => 'Администратор',
            'legal_form_id' => LegalForm::ID_OOO,
            'organization_name' => '',
            'organization_address' => '',
            'address' => '',
            'email' => static::ADMIN_EMAIL_ADDRESS,
            'phone' => '',
            'password' => bcrypt('admin'),
            'inn' => '',
            'kpp' => '',
            'ogrn' => '',
            'user_verify_status_id' => UserVerifyStatus::ID_APPROVED,
            'is_email_verified' => true,
        ]);
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::table('users')->where('email', static::ADMIN_EMAIL_ADDRESS)->delete();
    }
}
