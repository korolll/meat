<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Правила валидации загружаемых файлов (по категориям)
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'assortment-image' => 'mimes:jpeg,jpg,png|max:5120',

    'loyalty-card-type-logo' => 'mimes:jpeg,jpg,png|max:5120',

    'product-file' => 'mimes:jpeg,jpg,png,tiff,pdf|max:20480',

    'user-file' => 'mimes:jpeg,jpg,png,tiff,pdf|max:20480',

    'laboratory-test-file' => 'mimes:jpeg,jpg,png,tiff,pdf|max:20480',

    'assortment-file' => 'mimes:jpeg,jpg,png,tiff,pdf|max:20480',

    'story-image' => 'mimes:jpg,bmp,png,gif,svg',
    'story-tab-image' => 'mimes:jpg,bmp,png,gif,svg',

    'meal-receipt-file' => 'mimes:jpg,bmp,png,gif,svg,mp4,mpg|max:20480',

    'banner-image' => 'mimes:jpg,bmp,png,gif,svg',

];
