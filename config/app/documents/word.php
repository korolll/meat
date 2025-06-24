<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Данны для формирования договора поставки
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'supply_contract' => [
        'path' => env('APP_DOCUMENTS_SUPPLY_CONTRACT_PATH'),
        'user_uuids' => env_array('APP_DOCUMENTS_SUPPLY_CONTRACT_USER_UUIDS')
    ]

];
