<?php


use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorV5;

$atolVersion = (int)env('ATOL_ONLINE_VERSION', AtolSellRequestGeneratorV5::VERSION);
return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'stream-telecom' => [
        'username' => env('STREAM_TELECOM_USERNAME'),
        'password' => env('STREAM_TELECOM_PASSWORD'),
        'from' => env('STREAM_TELECOM_FROM'),
    ],

    'megafon-sms' => [
        'username' => env('MEGAFON_SMS_USERNAME'),
        'password' => env('MEGAFON_SMS_PASSWORD'),
        'from' => env('MEGAFON_SMS_FROM'),
    ],

    '1c' => [
        'product_exporter' => [
            'uri' => env('ONE_C_PRODUCT_EXPORTER_URI'),
            'token_header' => env('ONE_C_PRODUCT_EXPORTER_TOKEN_HEADER', 'ONE_S_AUTH_TOKEN'),
            'token' => env('ONE_C_PRODUCT_EXPORTER_TOKEN'),
        ],
        'product_request_exporter' => [
            'uri' => env('ONE_C_PRODUCT_REQUEST_EXPORTER_URI'),
            'token_header' => env('ONE_C_PRODUCT_REQUEST_EXPORTER_TOKEN_HEADER', 'ONE_S_AUTH_TOKEN'),
            'token' => env('ONE_C_PRODUCT_REQUEST_EXPORTER_TOKEN'),
        ],
        'users_allowed_to_export' => env_array('ONE_C_USERS_ALLOWED_TO_EXPORT'),

        'users_allowed_to_export_only_after_confirmed_date' => env_array('ONE_C_USERS_ALLOWED_TO_EXPORT_ONLY_AFTER_CONFIRMED_DATE'),

        'price_list_exporter' => [
            'uri' => env('ONE_C_PRICE_LIST_EXPORTER_URI'),
            'token_header' => env('ONE_C_PRICE_LIST_EXPORTER_TOKEN_HEADER', 'ONE_S_AUTH_TOKEN'),
            'token' => env('ONE_C_PRICE_LIST_EXPORTER_TOKEN'),
        ],
        'catalog_exporter' => [
            'uri' => env('ONE_C_CATALOG_EXPORTER_URI'),
            'token_header' => env('ONE_C_CATALOG_EXPORTER_TOKEN_HEADER', 'ONE_S_AUTH_TOKEN'),
            'token' => env('ONE_C_CATALOG_EXPORTER_TOKEN'),
        ],
    ],

    'atol' => [
        'online' => [
            'base_path' => env('ATOL_ONLINE_BASE_PATH',
                $atolVersion === AtolSellRequestGeneratorV5::VERSION
                    ? 'https://online.atol.ru/possystem/v5/'
                    : 'https://online.atol.ru/possystem/v4/'
            ),
            'version' => $atolVersion,
            'config' => [
                'group_code' => env('ATOL_ONLINE_GROUP_CODE'),
                'user' => env('ATOL_ONLINE_USER'),
                'password' => env('ATOL_ONLINE_PASSWORD'),
            ],
            'request_generator_conf' => [
                'item_vat' => env('ATOL_ONLINE_RGC_ITEM_VAT'),
                'delivery_vat' => env('ATOL_ONLINE_RGC_DELIVERY_VAT', 'none'),
                'company_inn' => env('ATOL_ONLINE_RGC_COMPANY_INN'),
            ]
        ],
        'export' => [
            'price_list' => [
                'uri' => env('ATOL_EXPORT_PRICE_LIST_URI'),
            ],
        ],
    ],

    'dadata' => [
        'suggestions' => [
            'api_key' => env('DADATA_SUGGESTIONS_API_KEY', ''),
        ]
    ],

    'guzzle_debug' => env('GUZZLE_DEBUG', false),

    'notifications' => [
        'product_pre_request_error_emails' => env_array('PRODUCT_PRE_REQUEST_ERROR_EMAILS'),
    ],

    'sberbank' => [
        'acquire' => [
            'bind_card_amount' => env('SBERBANK_ACQUIRE_BIND_CARD_AMOUNT', 100),
            'config' => [
                'userName' => env('SBERBANK_ACQUIRE_CONF_USERNAME'),
                'password' => env('SBERBANK_ACQUIRE_CONF_PASSWORD'),
                'language' => 'ru',
                'apiUri' => env('APP_ENV', 'development') === 'public' ? Voronkovich\SberbankAcquiring\Client::API_URI : Voronkovich\SberbankAcquiring\Client::API_URI_TEST,
                'currency' => Voronkovich\SberbankAcquiring\Currency::RUB,
                'httpMethod' => Voronkovich\SberbankAcquiring\HttpClient\HttpClientInterface::METHOD_POST,
            ]
        ]
    ],

    'yookassa' => [
        'acquire' => [
            'bind_card_amount' => env('YOOKASSA_ACQUIRE_BIND_CARD_AMOUNT', 100),
            'config' => [
                // Need authToken OR login + password
                'authToken' => env('YOOKASSA_ACQUIRE_CONF_AUTH_TOKEN'),
                'login' => env('YOOKASSA_ACQUIRE_CONF_USERNAME'),
                'password' => env('YOOKASSA_ACQUIRE_CONF_PASSWORD'),
            ]
        ]
    ],

    'iiko' => [
        'api_key' => env('IIKO_API_KEY'),
        'host' => env('IIKO_HOST', 'https://api-ru.iiko.services'),

        'delivery_product_id' => env('IIKO_DELIVERY_PRODUCT_ID'),
    ],

    'yandex' => [
        'disk' => [
            'token' => env('YANDEX_DISK_TOKEN'),
        ]
    ]
];
