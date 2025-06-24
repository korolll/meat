<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileCategory extends Model
{
    use SoftDeletes;

    /**
     * Изображение номенклатуры
     */
    const ID_ASSORTMENT_IMAGE = 'assortment-image';

    /**
     * Логотип типа карты лояльности
     */
    const ID_LOYALTY_CARD_TYPE_LOGO = 'loyalty-card-type-logo';

    /**
     * Логотип акции
     */
    const ID_PROMO_LOGO = 'promo-logo';

    /**
     * Логотип вакансии
     */
    const ID_VACANCY_LOGO = 'vacancy-logo';

    /**
     * Логотип онбординга
     */
    const ID_ONBOARDING_LOGO = 'onboarding-logo';

    /**
     * Логотип социальной сети
     */
    const ID_SOCIAL_LOGO = 'social-logo';

    /**
     * Файл продукта
     */
    const ID_PRODUCT_FILE = 'product-file';

    /**
     * Файл пользователя
     */
    const ID_USER_FILE = 'user-file';

    /**
     * Файлы лабораторных исследований заказчика
     */
    const ID_LABORATORY_TEST_FILE_CUSTOMER = 'labtest-file-customer';

    /**
     * Файлы лабораторных исследований исполнителя
     */
    const ID_LABORATORY_TEST_FILE_EXECUTOR = 'labtest-file-executor';

    /**
     * Файл номенклатуры
     */
    const ID_ASSORTMENT_FILE = 'assortment-file';

    /**
     * Изображение каталога
     */
    const ID_CATALOG_IMAGE = 'catalog-image';

    /**
     * Изображение магазина
     */
    const ID_SHOP_IMAGE = 'shop-image';

    /**
     * Изображение story
     */
    const ID_STORY_IMAGE = 'story-image';

    /**
     * Изображение banner
     */
    const ID_BANNER_IMAGE = 'banner-image';

    /**
     * Изображение/Видео meal-receipt
     */
    const ID_MEAL_RECEIPT_FILE = 'meal-receipt-file';

    /**
     * @var bool
     */
    public $incrementing = false;
}
