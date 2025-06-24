<?php

use App\Services\Framework\Database\Migrations\ManagesComments;
use Illuminate\Database\Migrations\Migration;

class MergeSchemaComments139 extends Migration
{
    use ManagesComments;

    /**
     * @return void
     */
    public function up()
    {
        $this->commentOnColumn('assortment_assortment_property', [
            'assortment_uuid' => 'Идентификатор асортимента',
            'assortment_property_uuid' => 'Идентификатор характеристики ассортимента',
            'value' => 'Значение характеристики',
        ]);

        $this->commentOnColumn('assortment_brands', [
            'uuid' => 'Идентификатор бренда',
            'name' => 'Наименование бренда',
        ]);

        $this->commentOnColumn('assortment_client_favorites', [
            'client_uuid' => 'Идентификатор клиента',
            'assortment_uuid' => 'Идентификатор ассортимента',
        ]);

        $this->commentOnColumn('assortment_client_shopping_list', [
            'client_shopping_list_uuid' => 'Идентификатор списка покупок',
            'assortment_uuid' => 'Идентификатор ассортимента',
            'quantity' => 'Количество',
        ]);

        $this->commentOnColumn('assortment_file', [
            'assortment_uuid' => 'Идентификатор номенлкатуры',
            'file_uuid' => 'Идентификатор файла',
            'public_name' => 'Публичное имя файла',
        ]);

        $this->commentOnColumn('assortment_matrices', [
            'user_uuid' => 'Идентификатор владельца ассортиментной матрицы',
            'assortment_uuid' => 'Идентификатор номенклатуры',
        ]);

        $this->commentOnColumn('assortment_properties', [
            'uuid' => 'Идентификатор характеристики асортимента',
            'name' => 'Наименование характеристики',
        ]);

        $this->commentOnColumn('assortment_property_catalog', [
            'catalog_uuid' => 'Идентификатор каталога',
            'assortment_property_uuid' => 'Идентификатор характеристики асортимента',
        ]);

        $this->commentOnColumn('assortment_units', [
            'id' => 'Идентификатор единицы измерения',
            'name' => 'Наименование измерения',
            'short_name' => 'Короткое наименование',
        ]);

        $this->commentOnColumn('assortment_verify_statuses', [
            'id' => 'Идентификатор статуса проверки продутка',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('assortments', [
            'uuid' => 'Идентификатор асортимента',
            'catalog_uuid' => 'Каталог Тилси',
            'barcode' => 'Штрих код',
            'name' => 'Наименование товара',
            'assortment_unit_id' => 'Единицы измерения',
            'country_id' => 'Страна производства',
            'okpo_code' => 'Код ОКПО',
            'weight' => 'Масса в граммах',
            'volume' => 'Объем в мм кубических',
            'ingredients' => 'Ингредиенты',
            'description' => 'Описание',
            'group_barcode' => 'Штрих код группы',
            'temperature_min' => 'Минимальная температура',
            'temperature_max' => 'Максимальная температура',
            'production_standard_id' => 'Идентификатор ГОСТ/ТУ',
            'production_standard_number' => 'Номер ГОСТ/ТУ',
            'is_storable' => 'Складируемый товар',
            'shelf_life' => 'Срок годности в днях',
            'nds_percent' => 'Ставка НДС',
            'assortment_verify_status_id' => 'Идентификатор статуса проверки',
            'manufacturer' => 'Производитель',
            'searchable' => 'Вектор полнотекстового поиска',
            'short_name' => 'Короткое наименование',
            'assortment_brand_uuid' => 'Идентификатор бренда',
        ]);

        $this->commentOnColumn('cars', [
            'uuid' => 'Идентификатор автомобиля',
            'user_uuid' => 'Идентификатор создавшего',
            'brand_name' => 'Марка автомобиля',
            'model_name' => 'Модель автомобиля',
            'license_plate' => 'Гос. номер',
            'call_sign' => 'Бортовой номер (позывной)',
            'max_weight' => 'Максимальная масса кг',
            'is_active' => 'Активна? (работает)',
        ]);

        $this->commentOnColumn('catalogs', [
            'uuid' => 'Идентификатор каталога',
            'user_uuid' => 'Владелец каталога',
            'catalog_uuid' => 'Родительский каталог',
            'name' => 'Наименование каталога',
            'level' => 'Уровень вложенности',
            'assortments_count' => 'Количество номенклатур, включая под каталоги',
        ]);

        $this->commentOnColumn('client_authentication_codes', [
            'uuid' => 'Идентификатор кода аутентификации',
            'client_uuid' => 'Идентификатор клиента',
            'code' => 'Код аутентификации',
        ]);

        $this->commentOnColumn('client_authentication_tokens', [
            'uuid' => 'Идентификатор токена аутентификации',
            'client_uuid' => 'Идентификатор клиента',
        ]);

        $this->commentOnColumn('client_shopping_lists', [
            'uuid' => 'Идентификатор списока покупок',
            'client_uuid' => 'Идентификатор клиента',
            'name' => 'Наименование списка',
        ]);

        $this->commentOnColumn('client_user_favorites', [
            'client_uuid' => 'Идентификатор клиента',
            'user_uuid' => 'Идентификатор пользователя (магазина)',
        ]);

        $this->commentOnColumn('clients', [
            'uuid' => 'Идентификатор клиента',
            'phone' => 'Номер телефона',
            'name' => 'Имя клиента',
        ]);

        $this->commentOnColumn('countries', [
            'id' => 'Идентификатор страны',
            'name' => 'Наименование страны',
        ]);

        $this->commentOnColumn('customer_product_request_supplier_product_request', [
            'customer_product_request_uuid' => 'Идентификатор заявки на поставку',
            'supplier_product_request_uuid' => 'Идентификатор заявки на отгрузку',
        ]);

        $this->commentOnColumn('drafts', [
            'uuid' => 'Идентификатор черновика',
            'user_uuid' => 'Идентификатор создавшего',
            'name' => 'Наименование черновика',
            'attributes' => 'Атрибуты черновика',
        ]);

        $this->commentOnColumn('drivers', [
            'uuid' => 'Идентификатор водителя',
            'user_uuid' => 'Идентификатор создавшего',
            'full_name' => 'ФИО',
            'password' => 'Пароль',
            'email' => 'Электронная поста',
            'hired_on' => 'Дата трудоустройства',
            'fired_on' => 'Дата увольнения',
            'comment' => 'Комментарий',
            'license_number' => 'Серия и номер ВУ',
        ]);

        $this->commentOnColumn('file_categories', [
            'id' => 'Идентификатор категории файла',
            'name' => 'Наименование категории файла',
        ]);

        $this->commentOnColumn('file_laboratory_test', [
            'laboratory_test_uuid' => 'Идентификатор лабораторного теста',
            'file_uuid' => 'Идентификатор файла',
            'file_category_id' => 'Идентификатор типа файла',
            'public_name' => 'Публичное имя файла',
        ]);

        $this->commentOnColumn('file_product', [
            'product_uuid' => 'Идентификатор товара',
            'file_uuid' => 'Идентификатор файла',
            'public_name' => 'Публичное имя файла',
        ]);

        $this->commentOnColumn('file_user', [
            'user_uuid' => 'Идентификатор пользователя',
            'file_uuid' => 'Идентификатор файла',
            'public_name' => 'Публичное имя файла',
        ]);

        $this->commentOnColumn('files', [
            'uuid' => 'Идентификатор файла',
            'user_uuid' => 'Идентификатор создавшего',
            'file_category_id' => 'Идентификатор категории файла',
            'original_name' => 'Исходное название',
            'path' => 'Путь к файлу',
            'mime_type' => 'Тип файла',
            'size' => 'Размер файла',
        ]);

        $this->commentOnColumn('laboratory_test_appeal_types', [
            'uuid' => 'Идентификатор типов обращений',
            'name' => 'Наименование типа обращения',
        ]);

        $this->commentOnColumn('laboratory_test_statuses', [
            'id' => 'Идентификатор статуса',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('laboratory_tests', [
            'uuid' => 'Идентификатор лабораторного теста',
            'laboratory_test_appeal_type_uuid' => 'Идентификатор типа обращения',
            'laboratory_test_status_id' => 'Идентификатор статуса',
            'customer_user_uuid' => 'Идентификатор заказчика (создателя)',
            'executor_user_uuid' => 'Идентификатор исполнителя',
            'assortment_supplier_user_uuid' => 'Идентификатор поставщика номенклатуры',
            'customer_full_name' => 'Имя заказчика',
            'customer_organization_name' => 'Наименование орг. заказчика',
            'customer_organization_address' => 'Юр. адрес орг. заказчика',
            'customer_inn' => 'ИНН заказчика',
            'customer_ogrn' => 'ОГРН заказчика',
            'customer_kpp' => 'КПП заказчика',
            'customer_position' => 'Должность представителя заказчика',
            'customer_bank_current_account' => 'РС банка заказчика',
            'customer_bank_correspondent_account' => 'КС банка заказчика',
            'customer_bank_name' => 'Наименование банка заказчика',
            'customer_bank_identification_code' => 'БИК банка заказчика',
            'assortment_barcode' => 'Штрих-код номенклатуры',
            'assortment_uuid' => 'Идентификатор номенклатуры',
            'assortment_name' => 'Наименование номенклатуры',
            'assortment_manufacturer' => 'Наименование производителя номенклатуры',
            'assortment_production_standard_id' => 'Идентификатор ГОСТ/ТУ номенклатуры',
            'batch_number' => 'Дата/номер патрии',
            'parameters' => 'Параметры исследования',
        ]);

        $this->commentOnColumn('legal_forms', [
            'id' => 'Идентификатор правовой формы',
            'name' => 'Наименование формы',
            'short_name' => 'Кратное наименование (ИП, ООО)',
        ]);

        $this->commentOnColumn('loyalty_card_type_user', [
            'loyalty_card_type_uuid' => 'Идентификатор типа карты лояльности',
            'user_uuid' => 'Идентификатор пользователя',
        ]);

        $this->commentOnColumn('loyalty_card_types', [
            'uuid' => 'Идентификатор типа карты лояльности',
            'name' => 'Наименование типа карты лояльности',
            'logo_file_uuid' => 'Идентификатор файла логотипа карты',
        ]);

        $this->commentOnColumn('loyalty_cards', [
            'uuid' => 'Идентификатор карты лояльности',
            'loyalty_card_type_uuid' => 'Идентификатор типа карты лояльности',
            'number' => 'Номер карты',
            'client_uuid' => 'Идентификатор клиетна',
            'discount_percent' => 'Процент скидки по карте',
        ]);

        $this->commentOnColumn('price_list_product', [
            'price_list_uuid' => 'Идентификатор прайс-листа',
            'product_uuid' => 'Идентификатор продукта пользователя',
            'price_old' => 'Старая цена',
            'price_new' => 'Новая цена',
        ]);

        $this->commentOnColumn('price_list_statuses', [
            'id' => 'Идентификатор статуса прайс-листа',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('price_lists', [
            'uuid' => 'Идентификатор прайс-листа',
            'user_uuid' => 'Идентификатор пользователя',
            'name' => 'Наименование прайс-листа',
            'price_list_status_id' => 'Флаг текущего прайс-листа',
            'date_from' => 'Дата начала действия',
            'date_till' => 'Дака окончания',
        ]);

        $this->commentOnColumn('product_product_request', [
            'product_request_uuid' => 'Идентификатор заявки',
            'product_uuid' => 'Идентификатор товара',
            'quantity' => 'Количество',
            'price' => 'Цена',
            'weight' => 'Вес',
            'volume' => 'Объем',
        ]);

        $this->commentOnColumn('product_request_customer_statuses', [
            'id' => 'Идентификатор статуса заказчика',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('product_request_delivery_methods', [
            'id' => 'Идентификатор способа доставки',
            'name' => 'Наименование способа доставки',
        ]);

        $this->commentOnColumn('product_request_delivery_statuses', [
            'id' => 'Идентификатор статуса перевозчика',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('product_request_supplier_statuses', [
            'id' => 'Идентификатор статуса поставщика',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('product_requests', [
            'uuid' => 'Идентификатор заявки на товары',
            'customer_user_uuid' => 'Идентификатор покупателя',
            'supplier_user_uuid' => 'Идентификатор поставщика',
            'delivery_user_uuid' => 'Идентификатор доставки',
            'product_request_customer_status_id' => 'Статус покупателя',
            'product_request_supplier_status_id' => 'Статус поставщика',
            'product_request_delivery_status_id' => 'Статус доставки',
            'price' => 'Общая цена',
            'weight' => 'Общий вес',
            'volume' => 'Общий объем',
            'transportation_uuid' => 'Идентификатор рейса',
            'expected_delivery_date' => 'Предполагаемая дата доставки',
            'product_request_delivery_method_id' => 'Способ доставки',
        ]);

        $this->commentOnColumn('product_stocktaking', [
            'stocktaking_uuid' => 'Идентификатор инвентаризации',
            'product_uuid' => 'Идентификатор продукта пользователя',
            'write_off_reason_id' => 'Идентификатор причины списания',
            'quantity_old' => 'Старое количество',
            'quantity_new' => 'Новое количество',
            'comment' => 'Комментарий',
        ]);

        $this->commentOnColumn('production_standards', [
            'id' => 'Идентификатор ГОСТ/ТУ',
            'name' => 'Наименование ГОСТ/ТУ',
            'short_name' => 'Короткое наименование',
        ]);

        $this->commentOnColumn('products', [
            'uuid' => 'Идентификатор товара',
            'user_uuid' => 'Идентификатор пользователя',
            'assortment_uuid' => 'Идентификатор номенклатуры',
            'catalog_uuid' => 'Идентификатор личного каталога пользователя',
            'quantum' => 'Квант',
            'min_quantum_in_order' => 'Минимальное количество квантов в заказе',
            'quantity' => 'Количество на остатке',
            'price' => 'Текущая цена',
            'min_delivery_time' => 'Время доставки в часах',
            'price_recommended' => 'Рекомендованная розничная цена',
            'delivery_weekdays' => 'Дни отгрузки товара',
        ]);

        $this->commentOnColumn('rating_scores', [
            'uuid' => 'Идентификатор оценки',
            'rated_reference_type' => 'Что оценивали',
            'rated_reference_id' => 'Идентификатор связанной модели',
            'rated_by_reference_type' => 'Что оценивал',
            'rated_by_reference_id' => 'Идентификатор связанной модели',
            'rated_through_reference_type' => 'Откуда поступила оценка',
            'rated_through_reference_id' => 'Идентификатор связанной модели',
            'value' => 'Оценка',
            'additional_attributes' => 'Атрибуты оценки, например комментарий',
        ]);

        $this->commentOnColumn('rating_types', [
            'id' => 'Идентификатор типа рейтинка',
            'name' => 'Наименование типа рейтинга',
        ]);

        $this->commentOnColumn('ratings', [
            'uuid' => 'Идентификатор рейтинга',
            'reference_type' => 'К чему относится рейтинг',
            'reference_id' => 'Идентификатор связанной модели',
            'rating_type_id' => 'Идентификатор типа рейтинга',
            'value' => 'Значение рейтинга',
            'additional_attributes' => 'Дополнительные данные для расчета',
        ]);

        $this->commentOnColumn('receipt_lines', [
            'uuid' => 'Идентификатор строки чека',
            'receipt_uuid' => 'Идентификатор чека',
            'product_uuid' => 'Идентификатор товара',
            'barcode' => 'Штрих код',
            'quantity' => 'Кол-во в чеке',
            'total' => 'Сумма в чеке',
            'assortment_uuid' => 'Идентификатор асортимента',
        ]);

        $this->commentOnColumn('receipts', [
            'uuid' => 'Идентификатор чека',
            'user_uuid' => 'Идентификатор пользователя-магазина',
            'receipt_package_id' => 'Порядковый номер пакета чеков',
            'id' => 'Порядковый номер чека',
            'loyalty_card_uuid' => 'Идентификатор карты лояльности',
            'loyalty_card_type_uuid' => 'Идентификатор типа карты лояльности',
            'loyalty_card_number' => 'Номер карты лояльности',
            'total' => 'Сумма чека',
            'received_at' => 'Дата получения чека бекендом',
        ]);

        $this->commentOnColumn('regions', [
            'uuid' => 'Идентификатор региона',
            'name' => 'Наименование типа',
        ]);

        $this->commentOnColumn('stocktakings', [
            'uuid' => 'Идентификатор инвентаризации',
            'user_uuid' => 'Идентификатор пользователя, проводившего инветаризацию',
            'approved_at' => 'Дата подтверждения инвентаризации',
        ]);

        $this->commentOnColumn('transportation_point_types', [
            'id' => 'Идентификатор типа точки рейса',
            'name' => 'Наименование типа',
        ]);

        $this->commentOnColumn('transportation_points', [
            'uuid' => 'Идентификатор точки рейса',
            'transportation_uuid' => 'Идентификатор рейса',
            'product_request_uuid' => 'Идентификатор заявки',
            'transportation_point_type_id' => 'Идентификатор типа точки',
            'address' => 'Адрес',
            'arrived_at' => 'Время прибытия',
            'order' => 'Порядок сортировки',
        ]);

        $this->commentOnColumn('transportation_statuses', [
            'id' => 'Идентификатор статуса рейса',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('transportations', [
            'uuid' => 'Идентификатор рейса',
            'user_uuid' => 'Идентификатор владельца',
            'date' => 'Дата рейса',
            'car_uuid' => 'Идентификатор автомобиля',
            'driver_uuid' => 'Идентификатор водителя',
            'transportation_status_id' => 'Идентификатор статуса рейса',
            'started_at' => 'Дата начала рейса',
            'finished_at' => 'Дата завершения рейса',
        ]);

        $this->commentOnColumn('user_additional_emails', [
            'uuid' => 'Идентификатор дополнительной электронной почты',
            'user_uuid' => 'Идентификатор пользователя',
            'email' => 'Электронная почта',
        ]);

        $this->commentOnColumn('user_password_resets', [
            'email' => 'Электронная почта',
            'token' => 'Токен для сброса пароля пользователя',
        ]);

        $this->commentOnColumn('user_types', [
            'id' => 'Идентификатор типа пользователя',
            'name' => 'Наименование типа',
        ]);

        $this->commentOnColumn('user_verify_statuses', [
            'id' => 'Идентификатор статуса проверки пользователя',
            'name' => 'Наименование статуса',
        ]);

        $this->commentOnColumn('users', [
            'uuid' => 'Идентификатор пользователя',
            'user_type_id' => 'Идентификатор типа пользователя',
            'full_name' => 'ФИО',
            'legal_form_id' => 'Идентификатор правовой формы',
            'organization_name' => 'Наименование организации',
            'organization_address' => 'Адрес организации',
            'address' => 'Адрес фактический',
            'email' => 'Электронная почта',
            'phone' => 'Телефон',
            'password' => 'Пароль',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'user_verify_status_id' => 'Идентификатор статуса профиля',
            'is_email_verified' => 'Электронная почта подтверждена?',
            'brand_name' => 'Наименование магазина',
            'address_latitude' => 'Широта расположения',
            'address_longitude' => 'Долгота расположения',
            'work_hours_from' => 'Время работы от',
            'work_hours_till' => 'Время работы до',
            'position' => 'Должность',
            'bank_correspondent_account' => 'Корреспондентский счет',
            'bank_current_account' => 'Расчетный счет',
            'bank_identification_code' => 'БИК',
            'bank_name' => 'Наименование банка',
            'region_uuid' => 'Идентификатор региона',
        ]);

        $this->commentOnColumn('warehouse_transactions', [
            'uuid' => 'Идентификатор транзакции',
            'product_uuid' => 'Идентификатор товара',
            'quantity_old' => 'Количество до операции',
            'quantity_delta' => 'Дельта операции',
            'quantity_new' => 'Количество после операции',
            'reference_type' => 'Что породило транзакцию',
            'reference_id' => 'Идентификатор связанной модели',
        ]);

        $this->commentOnColumn('write_off_reasons', [
            'id' => 'Идентификатор причины списания',
            'name' => 'Наименование причины списания',
        ]);

        $this->commentOnColumn('write_offs', [
            'uuid' => 'Идентификатор ручного списания',
            'user_uuid' => 'Идентификатор пользователя, который провел ручное списание',
            'product_uuid' => 'Идентификатор товара',
            'write_off_reason_id' => 'Идентификатор причины списания',
            'quantity_old' => 'Количество до операции',
            'quantity_delta' => 'Дельта операции',
            'quantity_new' => 'Количество после операции',
            'comment' => 'Комментарий',
        ]);
    }

    /**
     * @return void
     */
    public function down()
    {
        //
    }
}
