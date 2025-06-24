<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Уведомления по поводу даты окончания декларации
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'notifications' => [
        'declaration' => [
            // За сколько дней уведомлять об окончании декларации
            'notify_when_x_days_left' => env('APP_ASSORTMENTS_NOTIFICATIONS_DECLARATION_NOTIFY_WHEN_X_DAYS_LEFT', 10),

            // Уведомлять каждые Х дней о нуменклатуре с истекшей декларацией
            'notify_about_expired_each_x_days' => env('APP_ASSORTMENTS_NOTIFICATIONS_DECLARATION_NOTIFY_ABOUT_EXPIRED_EACH_X_DAYS', 7),
        ],
    ],
];
