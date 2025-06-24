<?php

return [
    'client_id' => env('AGENT_INFO_CLIENT_ID'),
    'type' => env('RECEIPT_AGENT_TYPE'),
    'paying_agent' => [
        'operation' => env('RECEIPT_AGENT_OPERATION'),
        'phones' => explode(',', env('RECEIPT_AGENT_PHONES')),
    ],
    'money_transfer_operator' => [
        'phones' => explode(',', env('RECEIPT_AGENT_OPERATOR_PHONES')),
        'name' => env('RECEIPT_AGENT_OPERATOR_NAME'),
        'address' => env('RECEIPT_AGENT_OPERATOR_ADDRESS'),
        'inn' => env('RECEIPT_AGENT_OPERATOR_INN'),
    ]
];